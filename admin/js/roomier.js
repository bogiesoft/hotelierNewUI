$( document ).ready(function() {
	var serviceURL="http://localhost/www.roomier.gr/"
	
	//$('#dashboard').hide();
	$('#dashboard').show();
	
	if(typeof(Storage) == "undefined") {
		// Sorry! No Web Storage support..
		window.location.replace("../hotelier/login.html");
	}
	
	if(localStorage.getItem("token") && localStorage.getItem("type").localeCompare("admin") == 0) {
		//$('#login-notify').html(resp.responce);
		//console.log("token " + localStorage.getItem("token"));
		//console.log("name " + localStorage.getItem("name"));
		$('body#bload').removeAttr('id');
		$('#username').html(localStorage.getItem("name") + " " + localStorage.getItem("surname"));
		$('[data-toggle="table2"]').attr('data-url', serviceURL+ 'admin.php');
		$('[data-toggle="table2"]').attr('data-dtype', 'hotelier');
		$('[data-toggle="table2"]').attr('data-action', 'show_users');
		
		
		
	} else {
		localStorage.clear();
		window.location.replace("../hotelier/login.html");
	}
	$("#logout").click(function(){
		localStorage.clear();
		window.location.replace("../hotelier/login.html");
	});
	
	var sprintf = function(str) {
        var args = arguments,
            flag = true,
            i = 1;

        str = str.replace(/%s/g, function () {
            var arg = args[i++];

            if (typeof arg === 'undefined') {
                flag = false;
                return '';
            }
            return arg;
        });
        if (flag) {
            return str;
        }
        return '';
    };
	
	/***
	 * Edit Users table (hotelier)
	 **/
	
	// Edit Name
	var  ch = false;
	var prevEle = '';
	$(document).on("click","._name_class_editable", function(e){
        
		var id=$(this).attr('id');
        e.stopPropagation();      //<-------stop the bubbling of the event here
        var value = $('#'+id).html();
        
		updateValName('#'+id, value);

     });
	 
	 //Edit User status
	 
	 $(document).on("click","._status_class_editable", function(e){
        
		var id=$(this).attr('id');
        e.stopPropagation();      //<-------stop the bubbling of the event here
        var value = $('#'+id).html();
        
		updateValStatus('#'+id, value);

     });
 
	/***
	 * Functions 
	 **/
	 
	function updateValName(currentEle, value) {
        console.log("Current Element is"+currentEle);
		if(!ch){
			$(currentEle).html(sprintf('<input class="thVal" type="text" value="%s" />', value));
			ch = true;
		}
        $(".thVal").focus();
        $(".thVal").keyup(function (event) {
            if (event.keyCode == 13) {
                $(currentEle).html($(".thVal").val().trim());
				ch = false;
            }
        });
        
        $(".thVal").focusout(function () { // you can use $('html')
            $(currentEle).html($(".thVal").val().trim());
			ch = false;
        });
		
    }
	
	function updateValStatus(currentEle, value) {
		var active = '';
		var non_active = '';
        //console.log("Current Element is"+currentEle);
		//console.log("checkbox:: "+$(currentEle).parent().find('input:checkbox').val());
				
		if(!ch){
			$(currentEle).html('<select class="thValSt"></select>');
			if(value.localeCompare('active') == 0)
				active = 'selected';
			else
				non_active = 'selected';
			
			$(".thValSt").append(sprintf('<option value="active" %s>active</option>', active));
			$(".thValSt").append(sprintf('<option value="non_active" %s>non_active</option>', non_active));
			ch = true;
		}
        $(".thValSt").focus();
		
		var updata = {
					"action": "update_user_data",
					"tbl" : "users",
					"id" :  $(currentEle).parent().find('input:checkbox').val(),
					"fldid" : "userID" ,
					"fld"  : "status",
					"val" : $(".thValSt").val()
				};
		
        $(".thValSt").keyup(function (event) {
            if (event.keyCode == 13) {
				/* var updata = {
					"action": "update_user_data",
					"tbl" : "users",
					"flddid" : "userID" ,
					"id" :  $(currentEle).parent().find('input:checkbox').val(),
					"fld"  : "status",
					"val" : $(".thValSt").val()
				}; */
				
                $(currentEle).html($(".thValSt").val());
				
				ch = false;
				console.log(" 2...");
				updateValBackEnd(updata);
            }
        });
        
        $(".thValSt").focusout(function () { // you can use $('html')
            $(currentEle).html($(".thValSt").val());
			ch = false;
			console.log("1 ...");
			updateValBackEnd(updata)
        });
		
    }
	
	function updateValBackEnd(updata){
		var data = {
				"action": updata.action,
				"email" : localStorage.getItem("email"),
				"token" : localStorage.getItem("token"),
				"tbl" : updata.tbl,
				"id" : updata.id,
				"fldid" : updata.fldid,
				"fld"  : updata.fld,
				"val" : updata.val
			};
			
		console.log("udata:: " + JSON.stringify(data));
		$.ajax({
           url: serviceURL+"admin.php",
           dataType: "json",
		   type: "post",
            data: JSON.stringify(data),
			success: function (resp) {
				//$('#login-notify').removeAttr('class');
				console.log(JSON.stringify(resp));
				//$('#dashboard').show();
            },
			error: function(err) {
				console.log("err " + JSON.stringify(err));
			}
       });
	
	}

});