<div class='wrap'>
<h2>Properties</h2>
<p><a href='?page=add_property'>Add New Property</a></p>
<?php

    //Create an instance of our package class...
    $testListTable = new Property_List();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items($properties);
	$testListTable->display();
?>
</div>