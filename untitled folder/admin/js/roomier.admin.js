$( document ).ready(function() {
		
	//$('#dashboard').hide();
	
	hideAll();
	$('#dashboard-content').show();
	$('#dashboard-content-stats').show();
	$('#dashboard-content-stats-pie').show();	
	
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
		
		//Users
		$('[data-toggle="table2"]').attr('data-url', serviceURL+ 'admin.php');
		$('[data-toggle="table2"]').attr('data-dtype', 'hotelier');
		$('[data-toggle="table2"]').attr('data-action', 'show_users');
		$('[data-toggle="table2"]').attr('data-refresh', 'refreshUsers');
		
		//Properties
		$('[data-toggle="properties"]').attr('data-url', serviceURL+ 'properties.php');
		$('[data-toggle="properties"]').attr('data-action', 'show_properties');
		//$('[data-toggle="properties"]').attr('data-add', 'addProperty');
		$('[data-toggle="properties"]').attr('data-refresh', 'refreshProperty');
		
		
		
	} else {
		localStorage.clear();
		window.location.replace("../hotelier/login.html");
	}
	
	$("#shDashboard").click(function(){
		hideAll();
		$('.page-header').html('Dashboard');
		$('#dashboard-content').show();
		$('#dashboard-content-stats').show();
		$('#dashboard-content-stats-pie').show();		
	});
	
	$('.glyphicon-remove').click(function(){
		$('.alert').hide();
	});
	
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
	 * Properties Administration
	 **/
	 
	$("#shProperty").click(function(){
		hideAll();
		$('.page-header').html('Users Properties');
		$('#propertiesTb').show();
	});
		
	var  ch = false;
	var prevEle = '';
	
	// Edit Alias
	$(document).on("click","._alias_class_editable", function(e){
        
		var id=$(this).attr('id');
        e.stopPropagation();      //<-------stop the bubbling of the event here
        var value = $('#'+id).html();
        
		updateValAllias('#'+id, value);

     });
	 
	 // Edit languages
	$(document).on("click","._langs_class_editable", function(e){
        
		var id=$(this).attr('id');
        e.stopPropagation();      //<-------stop the bubbling of the event here
        var value = $('#'+id).html();
        console.log("ch-a1::" + ch);
		updateValLangs('#'+id, value);
		console.log("ch-a2::" + ch);
     });
	
	var propertyID = 0;
	
	$(document).on("click",'.backGroundUpload', function(e){
        propertyID = $(this).data('property');
		$("input[name='propertyID']").val(propertyID);
		console.log("uploadbox:: " + propertyID);
		
		//$('#uploadbox').singleupload({});
		
		$('#uploadbox').trigger( "click" );		
		
     });
	
	
	$('#uploadbox').singleupload({
		action: serviceURL+'uploads/do_upload.php?action=background',//'do_upload.json', //action: 'do_upload.php'
		inputId: 'singleupload_input',
		previewClass: 'singleupload',
		onError: function(code) {
			console.debug('error code '+res.code);
		},
		onSuccess: function(url, data) {
			$('#return_url_text').val(serviceURL+url);
			console.debug('uploadbox '+serviceURL+url);
		}
		/*,onProgress: function(loaded, total) {} */
	});
	
	
	
	/***
	 * Edit Users table (hotelier)
	 **/
	
	$("#shUsers").click(function(){
		hideAll();
		$('.page-header').html('Users');
		$('#usersList').show();
	});
	
	
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
	 
	 function hideAll() {
		$('.alert').hide();
		$('#dashboard-content').hide();
		$('#propertiesTb').hide();
		$('#dashboard-content-stats').hide();
		$('#dashboard-content-stats-pie').hide();
		$('#usersList').hide();
	}
	 
	function updateValName(currentEle, value) {
        console.log("Current Element is"+currentEle);
		
		//Corrects corrupted flag after unexpected action
		if(ch && $( ".thVal" ).length == 0)
			ch = false;
		
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
	
	function updateValAllias(currentEle, value) {
        console.log("Current Element is"+currentEle);
		
		//Corrects corrupted flag after unexpected action
		if(ch && $( ".thVal" ).length == 0)
			ch = false;
		
		if(!ch){
			$(currentEle).html(sprintf('<input class="thVal" type="text" value="%s" />', value));
			ch = true;
		}
        $(".thVal").focus();
		
		
        $(".thVal").keyup(function (event) {
            if (event.keyCode == 13) {
				if (undefined != $(".thVal").val()) {
					var $body = $("body");
					$body.addClass("loading");
	
					var data = {
						"action": "update_property_alias",
						"email" : localStorage.getItem("email"),
						"token" : localStorage.getItem("token"),
						"propertyID" : $(currentEle).parent().find('input:checkbox').val(),
						"alias" : $(".thVal").val()
					};
					
					$(currentEle).html($(".thVal").val());
					console.log(JSON.stringify(data));
					ch = false;
					$.ajax({
					   url: serviceURL+ "properties.php",
					   dataType: "json",
					   type: "post",
						data: JSON.stringify(data),
						success: function (resp) {
							//$('#login-notify').removeAttr('class');
							console.log(JSON.stringify(resp));
							$('#refreshProperty').trigger( "click" );
							//$body.removeClass("loading");
							$( "#successTxt" ).html(resp.responce);
							$('.bg-success').show();
						},
						error: function(err) {
							console.log("err " + JSON.stringify(err));
							//$body.removeClass("loading");
							$('#dangerTxt').html(err);
							$('.bg-danger').show();
						}
				   });
				}
            }
        });
        
        $(".thVal").focusout(function () { // you can use $('html')
			if (undefined != $(".thVal").val()) {
				var $body = $("body");
				//$body.addClass("loading");
				
				var data = {
					"action": "update_property_alias",
					"email" : localStorage.getItem("email"),
					"token" : localStorage.getItem("token"),
					"propertyID" : $(currentEle).parent().find('input:checkbox').val(),
					"alias" : $(".thVal").val()
				};
				
				console.log(JSON.stringify(data));
				
				$(currentEle).html($(".thVal").val());
				ch = false;
				$.ajax({
				   url: serviceURL+ "properties.php",
				   dataType: "json",
				   type: "post",
					data: JSON.stringify(data),
					success: function (resp) {
						//$('#login-notify').removeAttr('class');
						console.log(JSON.stringify(resp));
						$('#refreshProperty').trigger( "click" );
						//$body.removeClass("loading");
						$( "#successTxt" ).html(resp.responce);
						$('.bg-success').show();
					},
					error: function(err) {
						console.log("err " + JSON.stringify(err));
						//$body.removeClass("loading");
						$('#dangerTxt').html(err);
						$('.bg-danger').show();
					}
			   });
			}
        });
		
    }
	
	
	function updateValLangs(currentEle, value) {
        //console.log("Current Element is"+currentEle);
		
		//Corrects corrupted flag after unexpected action
		if(ch && $( ".thVal" ).length == 0)
			ch = false;
		
		if(!ch){
			console.log("ch2::" + ch);
			$(currentEle).html(sprintf('<input class="thVal" type="text" value="%s" />', value));
			ch = true;
			console.log("ch3::" + ch);
		}
        $(".thVal").focus();
		
		console.log("ch4::" + ch + " - " + $( ".thVal" ).length);
        $(".thVal").keyup(function (event) {
			//console.log("ch5::" + ch);
            if (event.keyCode == 13) {
				ch = false;
				//console.log("ch6::" + ch);
				if (undefined != $(".thVal").val()) {
					//console.log("ch7::" + ch);
					var $body = $("body");
					$body.addClass("loading");
	
					var data = {
						"action": "update_property_langs",
						"email" : localStorage.getItem("email"),
						"token" : localStorage.getItem("token"),
						"propertyID" : $(currentEle).parent().find('input:checkbox').val(),
						"langs" : $(".thVal").val()
					};
					
					$(currentEle).html($(".thVal").val());
					//console.log(JSON.stringify(data));
					ch = false;
					$.ajax({
					   url: serviceURL+ "properties.php",
					   dataType: "json",
					   type: "post",
						data: JSON.stringify(data),
						success: function (resp) {
							//$('#login-notify').removeAttr('class');
							console.log(JSON.stringify(resp));
							$('#refreshProperty').trigger( "click" );
							//$body.removeClass("loading");
							$( "#successTxt" ).html(resp.responce);
							$('.bg-success').show();
							//console.log("ch8::" + ch);
						},
						error: function(err) {
							//console.log("ch9::" + ch);
							console.log("err " + JSON.stringify(err));
							//$body.removeClass("loading");
							$('#dangerTxt').html(err);
							$('.bg-danger').show();
						}
				   });
				}
            }
        });
        //console.log("ch10::" + ch);
        $(".thVal").focusout(function () { // you can use $('html')
			ch = false;
			if (undefined != $(".thVal").val()) {
				//console.log("ch11::" + ch);
				var $body = $("body");
				//$body.addClass("loading");
				var data = {
					"action": "update_property_langs",
					"email" : localStorage.getItem("email"),
					"token" : localStorage.getItem("token"),
					"propertyID" : $(currentEle).parent().find('input:checkbox').val(),
					"langs" : $(".thVal").val()
				};
				
				console.log(JSON.stringify(data));
				
				$(currentEle).html($(".thVal").val());
				$.ajax({
				   url: serviceURL+ "properties.php",
				   dataType: "json",
				   type: "post",
					data: JSON.stringify(data),
					success: function (resp) {
						//$('#login-notify').removeAttr('class');
						console.log(JSON.stringify(resp));
						$('#refreshProperty').trigger( "click" );
						//$body.removeClass("loading");
						$( "#successTxt" ).html(resp.responce);
						$('.bg-success').show();
						//console.log("ch12::" + ch);
					},
					error: function(err) {
						console.log("err " + JSON.stringify(err));
						//$body.removeClass("loading");
						$('#dangerTxt').html(err);
						$('.bg-danger').show();
						//console.log("ch13::" + ch);
					}
			   });
			}
			//console.log("ch14::" + ch);
        });
		//console.log("ch15::" + ch);
		
    }
	
	function updateValStatus(currentEle, value) {
		var active = '';
		var non_active = '';
        //console.log("Current Element is"+currentEle);
		//console.log("checkbox:: "+$(currentEle).parent().find('input:checkbox').val());
		
		//Corrects corrupted flag after unexpected action
		if(ch && $( ".thValSt" ).length == 0)
			ch = false;
				
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
				updateValBackEnd(updata,"admin.php");
            }
        });
        
        $(".thValSt").focusout(function () { // you can use $('html')
            $(currentEle).html($(".thValSt").val());
			ch = false;
			console.log("1 ...");
			updateValBackEnd(updata,"admin.php");
        });
		
    }
	
	function updateValBackEnd(updata,url){
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
           url: serviceURL+ url,
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