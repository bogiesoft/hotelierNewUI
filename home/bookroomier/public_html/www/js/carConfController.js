sgass.controller("carConfController", function($scope, $window, carTypeVersion, getRndNum, gdt, fetchImages, userSession, generateCarDetails, getImagesStatus, $q, $http, getPrice) {

    $("#hideCarConfModal").hide();
    $("#hideCarConfModal").click(function() {
        $("#hideCarConfModal").hide();
        sgass.mainNav.popPage();
        $(".cardetails").show();
    });

    var appendEquipment = {};

    if(sessionStorage.typnatcode !== sgass.mainNav.getCurrentPage().options.typnatcode) {
      sessionStorage.typnatcode = sgass.mainNav.getCurrentPage().options.typnatcode;
      sessionStorage.extraEquip = '';

    }

    var xmlhttp = new XMLHttpRequest();

    xmlhttp.open("POST", sprintf("%s://%s/services/private/car_open_info/server.php", urlPrefix, soap_api_url), true);
    var sr = sprintf(car_open_info, urlPrefix, soap_api_url, sgaas_user, sgaas_pass, sgass.mainNav.getCurrentPage().options.typnatcode);

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4) {
            if (xmlhttp.status == 200) {
                if (JSON.parse(xmlhttp.responseText).error == 'no_errors') {
                    console.log("confPricenew:: " + JSON.parse(xmlhttp.responseText).pricenew);
                    console.log("confPriceold:: " + parseInt($("#price").html()));
                    appendEquipment['initPrice'] = parseInt(JSON.parse(xmlhttp.responseText).pricenew);


                    //$("#car-conf-price").html(parseInt($("#price").html()) + " &euro;");
                    $("#car-conf-price").html(parseInt(JSON.parse(xmlhttp.responseText).pricenew) + " &euro;");
                    $("#price").html(parseInt(JSON.parse(xmlhttp.responseText).pricenew) + " &euro;");
                    $("#priceExtraEq").html("");
                    $("#extra-equip-show-in-details").html("");
                    $("#priceExtraEq").css("visibility", "hidden");
                    $("#extra-equip-show-in-details-hide").css("display", "none");
                    console.log("carConfController::$window.MODE_HISTORY:: " + $window.MODE_HISTORY);
                    if ($window.MODE_HISTORY == 'history_conf') {
                        var strToUpdate = '<span class="numb-left">(' + localStorage.getItem("sessionVisitsPerDayValuation") + '/' + localStorage.getItem("sessionVisitsPerDay") + ')</span>';
                        $(".update-all-user-info").html(strToUpdate);

                        var appendData = '';
                        $scope.gtPrms = sgass.mainNav.getCurrentPage().options.brandName;
                        $("#car-details-logo-conf").attr("src", "images/Make_Logos1/" + sgass.mainNav.getCurrentPage().options.brandNameReg + ".png");
                        var brandName = sgass.mainNav.getCurrentPage().options.brandName;
                        var brandNameReg = sgass.mainNav.getCurrentPage().options.brandNameReg;
                        var carConfDetails = document.getElementById("table-car-conf-details");
                        var extraEquiBuild = 'test';
                        var extraEquip = window.EXTRA_EQUIPMENT.ExtraEquip;
                        var extraEquiBuild = '';
                        var uniqueCount = 0;
                        $.each(extraEquip, function(key, val) {
                            ++uniqueCount;
                            extraEquiBuild += '<tr class="extra-equip-select" id="extraEqId-' + uniqueCount + '">';

                            $.each(val, function(k1, v1) {
                                if (k1 == 'equipment') {
                                    extraEquiBuild += '<td class="carDetConfLeft" id="equipmentKey-' + uniqueCount + '">' + v1 + '</td>';
                                } else {
                                    extraEquiBuild += '<td id="extraEqPriceId-' + uniqueCount + '" class="carDetConfRight">' + v1 + ' &euro; <span class="car-conf-add-button">+</span></td>';
                                }
                            });
                            extraEquiBuild += '</tr>';
                        });
                        carConfDetails.innerHTML = extraEquiBuild;
                        ons.compile(carConfDetails);
                        $scope.goBackConf = function() {

                            $(".cardetails").hide();
                            $("#hideCarConfModal").show();
                        }

                        //var appendEquipment = {};
                        //appendEquipment['initPrice'] = parseInt($("#car-conf-price").html());

                        $(".extra-equip-select").click(function() {

                            var getId = this.id.split('-')[1];
                            var eqKey = $("#equipmentKey-" + getId).html();
                            console.log("getId:: " + getId );
                            console.log("eqKey:: " + eqKey );
                            if (eqKey in appendEquipment) {

                                delete appendEquipment[eqKey];
                                $("#" + this.id).removeClass("extra-equip-selected");

                            } else {
                                $("#" + this.id).addClass("extra-equip-selected");
                                appendEquipment[eqKey] = parseInt($("#extraEqPriceId-" + getId).html());
                            }

                            var getResult = 0;
                            var finalPrice = 0;
                            var objCounter = 0;
                            var appendLiToDetails = "";
                            $.each(appendEquipment, function(kEq, vEq) {
                                finalPrice += parseInt(vEq);
                                ++objCounter;
                                if (kEq == 'initPrice') {} else {
                                    appendLiToDetails += '<li>' + kEq + ' <span class="exteqdet-price">+' + vEq + ' &euro; </span></li>';
                                }
                            });
                            $("#extra-equip-show-in-details").html(appendLiToDetails);

                            $("#car-conf-price").html(finalPrice + " &euro;");
                            $("#price").html(finalPrice + " &euro;");

                            $("#priceExtraEq").css("visibility", "visible");
                            $("#priceExtraEq").html("+" + (objCounter - 1));

                            $("#extra-equip-show-in-details-hide").css("display", "none");
                            var extEqDetHide = false;
                            $("#priceExtraEq").click(function() {
                                if (!extEqDetHide) {
                                    $("#extra-equip-show-in-details-hide").css("display", "table-row");
                                    extEqDetHide = true;
                                } else {
                                    $("#extra-equip-show-in-details-hide").css("display", "none");
                                    extEqDetHide = false;
                                }
                            });

                        });
                    } else {
                        // write
                        $.ajax({

                            type: 'POST',
                            url: 'http://sgaas.wpcloud.co/apic/get-state-write.php',
                            dataType: 'json',
                            data: {
                                sessionid: localStorage.getItem("sessionid"),
                                email: localStorage.getItem("sessiondbemail"),
                                modeflag: MODE_FLAG
                            },
                            success: function(data) {

                                if (data.responseText.visitsPerDayValuation === 0) {

                                    if (localStorage.getItem("userSignedIn")) {

                                        var msg = "Εχετε εξαντλήσει το όριο " + data.responseText.visitsPerDay + " την ημέρα";
                                        var carConfDetails = document.getElementById("table-car-conf-details");
                                        carConfDetails.innerHTML = msg;
                                        ons.compile(carConfDetails);
                                        $scope.goBackConf = function() {
                                            $("#hideCarConfModal").show();
                                        }

                                        localStorage.setItem('sessionVisitsPerDay', 0);
                                        $(".update-all-user-info").html('<span class="numb-left">(0/' + data.responseText.visitsPerDay + ')</span>');
                                    } else {

                                        var price = document.getElementById("price");
                                        price.innerHTML = '<span class="blue-color">Register to view price</span>';
                                        ons.compile(price);

                                    }
                                } else {

                                    if (localStorage.getItem("sessionVisitsPerDayValuation") == 0) {

                                        $(".update-all-user-info").html('<span class="numb-left">(0/' + localStorage.getItem("sessionVisitsPerDay") + ')</span>');
                                        var msg = "Εχετε εξαντλήσει το όριο " + localStorage.getItem("sessionVisitsPerDay") + " την ημέρα";
                                        var carConfDetails = document.getElementById("table-car-conf-details");
                                        carConfDetails.innerHTML = msg;
                                        ons.compile(carConfDetails);
                                        $scope.goBackConf = function() {
                                            $("#hideCarConfModal").show();
                                        }
                                    } else {

                                        // write
                                        localStorage.setItem("sessionVisitsPerDayValuation", (localStorage.getItem("sessionVisitsPerDayValuation") - 1));

                                        var strToUpdate = '<span class="numb-left">(' + localStorage.getItem("sessionVisitsPerDayValuation") + '/' + localStorage.getItem("sessionVisitsPerDay") + ')</span>';
                                        $(".update-all-user-info").html(strToUpdate);

                                        var appendData = '';
                                        $scope.gtPrms = sgass.mainNav.getCurrentPage().options.brandName;
                                        $("#car-details-logo-conf").attr("src", "images/Make_Logos1/" + sgass.mainNav.getCurrentPage().options.brandNameReg + ".png");
                                        var brandName = sgass.mainNav.getCurrentPage().options.brandName;
                                        var brandNameReg = sgass.mainNav.getCurrentPage().options.brandNameReg;
                                        var carConfDetails = document.getElementById("table-car-conf-details");
                                        var extraEquiBuild = 'test';
                                        var extraEquip = window.EXTRA_EQUIPMENT.ExtraEquip;
                                        var extraEquiBuild = '';
                                        var uniqueCount = 0;
                                        $.each(extraEquip, function(key, val) {
                                            ++uniqueCount;
                                            extraEquiBuild += '<tr class="extra-equip-select" id="extraEqId-' + uniqueCount + '">';

                                            $.each(val, function(k1, v1) {
                                                if (k1 == 'equipment') {
                                                    extraEquiBuild += '<td class="carDetConfLeft" id="equipmentKey-' + uniqueCount + '">' + v1 + '</td>';
                                                } else {
                                                    extraEquiBuild += '<td id="extraEqPriceId-' + uniqueCount + '" class="carDetConfRight">' + v1 + ' &euro; <span class="car-conf-add-button">+</span></td>';
                                                }
                                            });
                                            extraEquiBuild += '</tr>';
                                        });
                                        carConfDetails.innerHTML = extraEquiBuild;
                                        ons.compile(carConfDetails);
                                        $scope.goBackConf = function() {

                                            $(".cardetails").hide();
                                            $("#hideCarConfModal").show();
                                        }

                                        //var appendEquipment = {};
                                        //appendEquipment['initPrice'] = parseInt($("#car-conf-price").html());

                                        $(".extra-equip-select").click(function() {

                                            var getId = this.id.split('-')[1];
                                            var eqKey = $("#equipmentKey-" + getId).html();
                                            console.log("getId:: " + getId );
                                            console.log("eqKey:: " + eqKey );
                                            if (eqKey in appendEquipment) {

                                                delete appendEquipment[eqKey];
                                                $("#" + this.id).removeClass("extra-equip-selected");

                                            } else {
                                                $("#" + this.id).addClass("extra-equip-selected");
                                                appendEquipment[eqKey] = parseInt($("#extraEqPriceId-" + getId).html());
                                            }

                                            var getResult = 0;
                                            var finalPrice = 0;
                                            var objCounter = 0;
                                            var appendLiToDetails = "";
                                            $.each(appendEquipment, function(kEq, vEq) {
                                                finalPrice += parseInt(vEq);
                                                ++objCounter;
                                                if (kEq == 'initPrice') {} else {
                                                    appendLiToDetails += '<li>' + kEq + ' <span class="exteqdet-price">+' + vEq + ' &euro; </span></li>';
                                                }
                                            });
                                            $("#extra-equip-show-in-details").html(appendLiToDetails);

                                            $("#car-conf-price").html(finalPrice + " &euro;");
                                            $("#price").html(finalPrice + " &euro;");

                                            $("#priceExtraEq").css("visibility", "visible");
                                            $("#priceExtraEq").html("+" + (objCounter - 1));

                                            $("#extra-equip-show-in-details-hide").css("display", "none");
                                            var extEqDetHide = false;
                                            $("#priceExtraEq").click(function() {
                                                if (!extEqDetHide) {
                                                    $("#extra-equip-show-in-details-hide").css("display", "table-row");
                                                    extEqDetHide = true;
                                                } else {
                                                    $("#extra-equip-show-in-details-hide").css("display", "none");
                                                    extEqDetHide = false;
                                                }
                                            });

                                        });

                                    }
                                }
                            },
                            error: function(error) {
                                alert("error");
                                console.log(error);
                            }
                        });
                    }

                }
            }
        }
    };
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(sr);
});
