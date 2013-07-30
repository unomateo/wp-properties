<style>
.container-fluid {
  margin-right: auto;
  margin-left: auto;
  max-width: 800px; /* or 950px */
}
</style>

<div class='container-fluid property_container' data-id="<?php echo $id ?>">
  <div class="row-fluid">

    <div class="span12 alert">
      <a href='#'>Preview</a>
    </div>

    <div class="span12">
      <h2><?php echo $property->address?></h2>
    </div>



    <div class="row-fluid">
      <div class="span12">
        <a href="#" class="thumbnail main upload_image_button" data-multiple='false'>
          <img id='main_image' src='<?php echo ($main_image!=null)?$main_image:'' ?>' data-src="holder.js/100%x350" alt="" />
        </a>
      </div>
    </div>

   

    <div class="row-fluid">
      <div class="span12" style='margin-top:5px;'>
        <p><a href='#' class="upload_image_button" data-multiple='true'>Add Images</a></p>
          <ul class="thumbnails span12" id='property_image_ul'>
            <?php if($thumbnails): foreach($thumbnails as $thumbnail): ?>
              <li class='span1'><a href='#' class='thumbnail'><img src='<?php echo $thumbnail->image_url ?>' alt=""></a></li>
            <?php endforeach; endif; ?>
          </ul>
      </div>
      <form action='' method='post'>
        <div id='description' class="span12">
          <h4>Description</h4>
            <textarea class='form_control span12' name='description'><?= $property->description?></textarea>
        </div>
      

        <div class="row-fluid">
          <div class='span6'>
            Status: <select name='status'>
              <option value='Active'>Active</option>
              <option value='Pending'>Pending</option>
              <option value='Sold'>Sold</option>
            </select>
          </div>

          <div class='span6'>
            Price: <input type='text' name='price' value='<?= $property->price?>' />
          </div>
      </div>
      <input type='hidden' value='<?= $property->id?>' name='id' />
      <p><input class='btn btn-primary' type="submit" value='submit' name='submit' /></p>
    <form>

  </div>
</div>