<?php
@include_once  '../../../common.php';

$stl = "<style>

#slider {
  position: relative;
  width: 500px;
  max-height: 300px;
  overflow: hidden;
  margin: 0 auto 0 auto;
  border-radius: 5px 0 0 5px;
}

#slider ul {
  position: relative;
  margin: 0;
  padding: 0;
  height: 200px;
  list-style: none;
}

#slider ul li {
  position: relative;
  display: block;
  float: left;
  margin: 0;
  padding: 0;
  width: 500px;
  height: 300px;
  background: #ccc;
  text-align: center;
  line-height: 300px;
}

span.control_prev, span.control_next {
  position: absolute;
  top: 40%;
  z-index: 999;
  display: block;
  padding: 4% 3%;
  width: auto;
  height: auto;
  background: #2a2a2a;
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  font-size: 18px;
  opacity: 0.8;
  cursor: pointer;
}

span.control_prev:hover, span.control_next:hover {
  opacity: 1;
  -webkit-transition: all 0.2s ease;
}

span.control_prev {
  border-radius: 0 2px 2px 0;
}

span.control_next {
  right: 0;
  border-radius: 2px 0 0 2px;
}

.slider_option {
  position: relative;
  margin: 10px auto;
  width: 160px;
  font-size: 18px;
}
</style>";
$scr = "<script type='text/javascript'>
jQuery(document).ready(function ($) {

  $('#modal-room').hide();

  $('#checkbox').change(function(){
    setInterval(function () {
        moveRight();
    }, 3000);
  });

	var slideCount = $('#slider ul li').length;
	var slideWidth = $('#slider ul li').width();
	var slideHeight = $('#slider ul li').height();
	var sliderUlWidth = slideCount * slideWidth;

	$('#slider').css({ width: slideWidth, height: slideHeight });

	$('#slider ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });

  $('#slider ul li:last-child').prependTo('#slider ul');

  $('span.control_prev').click(function () {
      moveLeft();
  });

  $('span.control_next').click(function () {
      moveRight();
  });

  $('.close').on('click', function(){
    $('#modal-room').hide();
  });

  $('.myBtn').on('click', function(){
    $('[id^=modal-roo]').hide();
    $('#modal-room').show();
  });

  //functions
  function moveLeft() {
      $('#slider ul').animate({
          left: + slideWidth
      }, 200, function () {
          $('#slider ul li:last-child').prependTo('#slider ul');
          $('#slider ul').css('left', '');
      });
  };

  function moveRight() {
      $('#slider ul').animate({
          left: - slideWidth
      }, 200, function () {
          $('#slider ul li:first-child').appendTo('#slider ul');
          $('#slider ul').css('left', '');
      });
  };

});
</script>";
$slide = '<div id="slider">
  <span class="control_next">>></span>
  <span class="control_prev"><</span>
  <ul  class="roomImg">
  </ul>
</div>';
//<td id="roomImg">'.$slide.'</td>
echo $stl.$scr.'<div class="room-dummy">
  <table  id="roomgrid">
    <tr>
      <td id="roomImg" class="roomImg_thump"></td>
      <td id="roomAvailTabs"  class="room_desc">
        <div class="room-header"></div>
        <button class="btn btn-primary myBtn">'.$lang['MORE_INFO'].'</button>
        <div id="modal-room">
          <!-- Modal content -->
          <div class="modal-content">
            <div class="modal-content-body">
            <div class="modal-header">
               <div class="room-header-2"></div>
               <div class="bookCart"><span class="close">×</span></div>
            </div>
            <div class="modal-body">

              <ul class="nav nav-pills">
               <li class="active"><a href="#pilltab1" data-toggle="tab">'.$lang['ROOM_INFO'].'</a></li>
               <li><a href="#pilltab2" data-toggle="tab">'.$lang['STANDARD_RATE'].'</a></li>
               <!--li><a href="#pilltab3" data-toggle="tab">'.$lang['ROOM_AVAILOABILITY'].'</a></li-->
             </ul>
              <div class="tab-content">
               <div class="tab-pane fade in active" id="pilltab1">
                  ' . $slide . '
                  <div class="roomDesc"></div>
               </div>
               <div class="tab-pane fade pilltab2" id="pilltab2">
                      <span class="break-down">'.$lang['DAILY_RATE_BREAKDOWN'].':</span><span class="check-in-out"></span>
                  <!--table id="datePriceRate">
                      <tr class="Row1">
                      </tr>
                      <tr class="Row2">
                      </tr>
                  </table-->
               </div>
               <!--div class="tab-pane fade" id="pilltab3">
                  <div id="calendar"></div>
               </div-->
             </div>
            </div>
            </div>
            <!--div class="modal-footer">
              <h3>Modal Footer</h3>
            </div-->
          </div>
        </div>
      <td>
      <td class="RowRes Row3"></td>
    </tr>
  </table>
</div>';
?>
