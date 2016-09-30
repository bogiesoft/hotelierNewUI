<?php
include_once  '../../../common.php';
?>
<div class="room">
  <table id="roomgrid">
      <tr class="Row1">
          <td rowspan="2" id="roomImg" class="roomImg">&nbsp;</td>
          <td rowspan="2" id="roomDesc" class="roomDesc"></td>
      </tr>
      <tr class="Row2">

      </tr>
  </table>
  <div class="totalBook">
    <span class="totalCost"></span>
    <button class="btn btn-primary"  class="bookRoom" name="booking-form-check"><?php echo $lang['BOOK_NOW']; ?></button>
  </div>
</div>
