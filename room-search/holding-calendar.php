
<?php
add_shortcode('HoldingCalOasis', 'HoldingCalOasis');
// manager Function - Used by shortcode 
function HoldingCalOasis() { ?>

	<div class="checkclass"></div>

	<div id="resultscontainer" class="JO">	
		<div class="resultsmain">
			<div id="resultswrap">
				<div class="calwrap">
					<button class="calarrow joarrow prev disabled" id="prev"></button>
					<div class="resultscalendarholdJO"></div>
					<button class="calarrow joarrow next" id="next"></button>
				</div>
			</div> 
		</div>
	</div>

<?php		
}
?>


<?php
add_shortcode('HoldingCalMountain', 'HoldingCalMountain');
// manager Function - Used by shortcode 
function HoldingCalMountain() { ?>

	<div class="checkclass"></div>

	<div id="resultscontainer" class="JM">	
		<div class="resultsmain">
			<div id="resultswrap">
				<div class="calwrap">
					<button class="calarrow jmarrow prev disabled" id="prev"></button>
					<div class="resultscalendarholdJM"></div>
					<button class="calarrow jmarrow next" id="next"></button>
				</div>
			</div> 
		</div>
	</div>

<?php		
}
?>

