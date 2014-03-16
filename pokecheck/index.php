<!doctype HTML public "-//W3O//DTD W3 HTML 2.0//EN">
<html>
    <head>
	<title>Pokecheck</title>
	<link type ="text/css" rel="stylesheet" href = "stylesheet.css">
    </head>
	<body OnLoad="document.searchbar.pokeName.focus();" align="center">
<!--		<div id="header" align="center">
			<h1>Pokecheck</h1>
		</div>-->
		<div id = "page" align="center">
			<form action = "index.php" method = "get" name = "searchbar">
				<p>Pokemon name: <input type = "text" name = "pokeName">
				<input type="submit"/></p>
			</form>
			<?php
			
				//if a name has been submitted
				if ( isset ($_GET["pokeName"]))
				{
					//first, read in the index which links names and pokedex numbers
					$index = json_decode(file_get_contents("api/index.txt"));
					$pokeName = strtolower($_GET["pokeName"]);
					//determine pokedex number
					if (property_exists($index, $pokeName))
					{
						//gather pokemon data
						$dexnum = $index -> $pokeName;
						$url = "api/".$dexnum.'.txt';
						$json = file_get_contents($url);
						$pokemon = json_decode($json);
						$sprite = "sprites/".$dexnum.".png";
						$typeInfo = $pokemon -> {"types"};
						
						//output
						?>
						<img src = <?php echo $sprite ?> ><br>
						<h2><?php echo $pokemon->{"name"} ?></h2>
						<?php printTypes($typeInfo); ?>
						<br>
						<br>
						Abilities: <?php printAbilities($pokemon )?><br>
						Max Speed: <?php echo calcMaxStats($pokemon->{"speed"}, 50) ?><br>
						<?php
						//output battle subway builds
						$builds = json_decode(file_get_contents("subwaybuilds.txt"));
						if (property_exists($builds,$pokeName)) {movetable($pokeName, $builds);}
						defenseArray($typeInfo);
						
					}else {echo '"'.$pokeName.'" not found.';}
				}
					
						?>
			<br> 
			
		<!-- FUNCTIONS -->
		<?php
			function calcMaxStats($base, $level)
			{
				$maxStat = floor(floor(((31 + (2*$base) + 252/4) * $level)/100 + 5) * 1.1);
				return $maxStat;
			}
			
			function printAbilities($pokemon)
			{
				$i = 0;
				$abilities = $pokemon->{"abilities"};
				
				for ( $i == 0; $i < count($abilities); $i++)
				{
					echo $abilities[$i]->{"name"}.' ';
				}
			}
			
			function movetable($pokeName, $builds)
			{
				$build = $builds->{$pokeName};
				$num_builds = count($build);
				echo '<br>';
				echo '<table border="1">';
				$i = 0;
				for ($i==0; $i<$num_builds; $i++)
				{
					echo '
					<tr>
					<td>'.$build[$i]->{"Item"}.'</td>
					<td>'.$build[$i]->{"Moves"}[0].'</td>
					<td>'.$build[$i]->{"Moves"}[1].'</td>
					<td>'.$build[$i]->{"Moves"}[2].'</td>
					<td>'.$build[$i]->{"Moves"}[3].'</td>
					<td>'.$build[$i]->{"Nature"}.'</td>
					<td>'.$build[$i]->{"EVs"}[0].'/'.$build[0]->{"EVs"}[1].'</td>
					</tr>';
				}
				echo '</table>';
			}
			
			function printTypes($typeInfo)
			{
				$num_types = count($typeInfo);
				$i = 0;
				for ($i==0; $i<$num_types; $i++)
				{
					echo '<div class = "type-'.$typeInfo[$i]->{"name"}.'">'.$typeInfo[$i]->{"name"}.' </div>';
				}
			}
			
			function defenseArray($typeInfo)
			{
				$num_types = count($typeInfo);
				$i = 0;
				$defArrayOld = array(1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
				for ($i==0; $i<$num_types; $i++)
				{
					$defArray = array_map(function($x, $y) { return $x * $y; }, typeChart()[array_search($typeInfo[$i]->{"name"},typeNum())], $defArrayOld);
					$defArrayOld = $defArray;
				}
				?>
				<table border="1">
					<?php $fourWeakness = array_keys($defArray, $search_value = 4); ?>
					<tr><td>4x</td><td><?php $i=0; for ($i==0; $i<count($fourWeakness); $i++) echoType(typeNum()[$fourWeakness[$i]]).' '; ?></td></tr>
					<?php $twoWeakness = array_keys($defArray, $search_value = 2); ?>
					<tr><td>2x</td><td><?php $i=0; for ($i==0; $i<count($twoWeakness); $i++) echoType(typeNum()[$twoWeakness[$i]]).' '; ?></td></tr>
					<?php $oneWeakness = array_keys($defArray, $search_value = 1); ?>
					<tr><td>1x</td><td><?php $i=0; for ($i==0; $i<count($oneWeakness); $i++) echoType(typeNum()[$oneWeakness[$i]]).' '; ?></td></tr>
					<?php $halfWeakness = array_keys($defArray, $search_value = 0.5); ?>
					<tr><td>0.5x</td><td><?php $i=0; for ($i==0; $i<count($halfWeakness); $i++) echoType(typeNum()[$halfWeakness[$i]]).' '; ?></td></tr>
					<?php $quartWeakness = array_keys($defArray, $search_value = 0.25); ?>
					<tr><td>0.25x</td><td><?php $i=0; for ($i==0; $i<count($quartWeakness); $i++) echoType(typeNum()[$quartWeakness[$i]]).' '; ?></td></tr>
					<?php $zeroWeakness = array_keys($defArray, $search_value = 0); ?>
					<tr><td>0x</td><td><?php $i=0; for ($i==0; $i<count($zeroWeakness); $i++) echoType(typeNum()[$zeroWeakness[$i]]).' '; ?></td></tr>
				</table>
				<?php

			}

			function echoType($type)
			{
				echo '<div class = "type-'.$type.'">'.$type.' </div>';
			}
			
			function typeChart()
			{
				return array(
						// n,fi,fl,po,gr,ro,bu,gh,st,fr,wa,gr,el,ps,ic,dr,da,fa
					array( 1, 2, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),//norm
					array( 1, 1, 2, 1, 1,.5,.5, 1, 1, 1, 1, 1, 1, 2, 1, 1,0.5, 2),//fight
					array( 1,.5, 1, 1, 0, 2,.5, 1, 1, 1, 1,.5, 2, 1, 2, 1, 1, 1),//fly
					array( 1,.5, 1,.5, 2, 1,.5, 1, 1, 1, 1,.5, 1, 2, 1, 1, 1,.5),//pois
					array( 1, 1, 1,.5, 1,.5, 1, 1, 1, 1, 2, 2, 0, 1, 2, 1, 1, 1),//grou
					array(.5, 2,.5,.5, 2, 1, 1, 1, 2,.5, 2, 2, 1, 1, 1, 1, 1, 1),//rock
					array( 1,.5, 2, 1,.5, 2, 1, 1, 1, 2, 1,.5, 1, 1, 1, 1, 1, 1),//bug
					array( 0, 0, 1,.5, 1, 1,.5, 2, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1),//gho
					array(.5, 2,.5, 0, 2,.5,.5, 1,.5, 2, 1,.5, 1,.5,.5,.5, 1,.5),//ste
					array( 1, 1, 1, 1, 2, 2,.5, 1,.5,.5, 2,.5, 1, 1,.5, 1, 1,.5),//fire
					array( 1, 1, 1, 1, 1, 1, 1, 1,.5,.5,.5, 2, 2, 1,.5, 1, 1, 1),//water
					array( 1, 1, 2, 2,.5, 1, 2, 1, 1, 2,.5,.5,.5, 1, 2, 1, 1, 1),//gras
					array( 1, 1,.5, 1, 2, 1, 1, 1,.5, 1, 1, 1,.5, 1, 1, 1, 1, 1),//elec
					array( 1,.5, 1, 1, 1, 1, 2, 2, 1, 1, 1, 1, 1,.5, 1, 1, 2, 1),//psy
					array( 1, 2, 1, 1, 1, 2, 1, 1, 2, 2, 1, 1, 1, 1,.5, 1, 1, 1),//ice
					array( 1, 1, 1, 1, 1, 1, 1, 1, 1,.5,.5,.5,.5, 1, 2, 2, 1, 2),//dra
					array( 1, 2, 1, 1, 1, 1, 2,.5, 1, 1, 1, 1, 1, 0, 1, 1,.5, 2),//dark
					array( 1,.5, 1, 2, 1, 1,.5, 1, 2, 1, 1, 1, 1, 1, 1, 0,.5, 1)//fair
				);
			}
			
			function typeNum()
			{
				return array('normal','fighting','flying','poison','ground','rock','bug','ghost','steel','fire','water','grass','electric','psychic','ice','dragon','dark','fairy');
			}
			
		?>
		</div>
	</body>
</html>
