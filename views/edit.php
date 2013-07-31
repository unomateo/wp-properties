<style>
.container-fluid {
  margin-right: auto;
  margin-left: auto;
  width: 800px; /* or 950px */
}

.fields{
  position:absolute;
  left:0px;
  width:200px;
}
.main{
  margin-left:10px;
  width:100%;
}

.thumbnail img{
  height:60px;
  border:1px solid #999;
  padding:3px;
  margin:2px;
}

label{
  font-weight: bold;
}

.thumbnails li{
display: inline;
list-style-type: none;
padding-right: 20px;
}
</style>

<div class='container-fluid property_container' data-id="<?php echo $id ?>">

<div class="span12">
      <h2><?php echo $property->address?> <?php echo $property->city?>, <?php echo $property->state?></h2>
</div>

<div class='fields'>
  <form method='post'>
    <div>
      <label>Price</label><br>
      <input type='text' name='price' value='<?= $property->price ?>' />
    </div>
    <div>
      <label>Status</label><br>
      <select name='status'>
        <option value='Active'>Active</option>
        <option value='Pending'>Pending</option>
        <option value='Sold'>Sold</option>
      </select>
    </div>
    <div>
      <label>Bedrooms</label><br>
      <input type='text' name='bedrooms' value='<?= $property->bedrooms?>' />
    </div>
    <div>
      <label>Bathrooms</label><br>
      <input type='text' name='bathrooms' value='<?= $property->bathrooms?>' />
    </div>
    <div>
      <label>Year Built</label><br>
      <input type='text' name='yearBuilt' value='<?= $property->yearBuilt?>' />
    </div>
    <div>
      <label>Lot Size</label><br>
      <input type='text' name='lotSizeSqFt' value='<?= $property->lotSizeSqFt?>' />
    </div>
    <div>
      <label>House Size</label><br>
      <input type='text' name='finishedSqFt' value='<?= $property->finishedSqFt?>' />
    </div>
    
 
</div>

<div class='main'>
  
      <a href="#" class="main upload_image_button" data-multiple='false'>
       <img id='main_image' style='width:100%' src='<?php echo ($main_image!=null)?$main_image:'' ?>' data-src="holder.js/100%x350" alt="" />
     </a>

      <div><a href='#' class="upload_image_button" data-multiple='true'>Add Images</a></p>
          <ul class="thumbnails" id='property_image_ul'>
            <?php if($thumbnails): foreach($thumbnails as $thumbnail): ?>
              <li><a href='#' class='thumbnail'><img src='<?php echo $thumbnail->image_url ?>' alt=""></a></li>
            <?php endforeach; endif; ?>
          </ul>
      </div>

        <div id='description' class="span12">
          <h4>Description</h4>
            <textarea style='width:100%; height:200px' name='description'><?= $property->description?></textarea>
        </div>
</div>

      <input type='hidden' value='<?= $property->id?>' name='id' />
      <p><input class='btn btn-primary' type="submit" value='submit' name='submit' /></p>
    <form>

</div>