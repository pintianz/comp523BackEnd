
var orders = [];
var user;
var baseUrl = "https://secure28.webhostinghub.com/~pocket14/forsyth.im/caterpillars/";

$(document).ready(function(){
    resetForm();
    $("select[name=temperature]").prop("selectedIndex", -1);  
    //retrieving user info from server (if user has logged in)
    //If user has not logged in, redirect to login page
    $.ajax(baseUrl + "login.php", {
        type: "GET",
        async: false,
        success: function (data, status, xhr){
            user = data;
            $("span.userName").html(user.name);
            $("span.userEmail").html(user.email);
        },
        error: function (xhr, status) {
            window.location.replace("login.html");
        }
    });
    
    /*
     * At the stage, we have verified that user is logged in
     */
    
    //Retrieve all site information and fill site selector
    //SiteID is stored in value of options
    $.ajax({
       url: baseUrl + "sites.php",
       type: "post",
       dataType: "json",
       data: JSON.stringify({action: "getAllSiteState"}),
       success: function(sites, xhr, status){
           var $siteSelect = $("select[name=site]");
           for(var i = 0; i < sites.length; i++){
               $siteSelect.append("<option value= '" + sites[i].siteID + "'>" + sites[i].siteID + ": " + sites[i].siteName + ", " + sites[i].siteState + "</option>");
           }
           
           $siteSelect.prop("selectedIndex", -1);
        }
    });
    
    //Sign out button
    $("#signout").click(function (e) {
        $.ajax({
            url: baseUrl + "login.php?signout=1",
            type: "GET",
            success: function (data, xhr, status) {
                window.location.replace("login.html");
            }
        });
    });
    
    //Responding to add order button
    //Add a new empty order form
    $("#addOrderForm").click(function(e){
       e.preventDefault();
       var $orderCard = createNewOrder();
       $("div.orderList").append($orderCard);
    });
    
    //Cancel and delete the order form
    $("div.orderList").on("click", "button.cancel", function(e){
       e.stopPropagation();
       e.preventDefault();
       $orderDiv = $(this).closest("div");
       $orderDiv.remove();
    });
    
    //add completed order card to order list
    $("div.orderList").on("click", "button.addOrderCard", function(e){
        e.stopPropagation();
        e.preventDefault();
        $form = $(this).parent();
        var arthropod = $form.find(".arthropod").val();
        var length = $form.find(".length").val();
        var notes = $form.find(".orderNotes").val();
        var count = $form.find(".count").val();
        
        if(!arthropod){
            alert("Please select an arthropod order.");
            return;
        }
        if(!length){
            alert("Please enter a length.");
            return;
        }
        if(!count){
            alert("Please enter a count.");
            return;
        }
        
        if(isNaN(length)){
            alert("Length must be a number.");
            return;
        }
        
        if(isNaN(count)){
            alert("Count must be a number.");
            return;
        }
        
        var newOrder = {
          arthropod: arthropod,
          length: length,
          notes: notes,
          count: count
        };
        
        var i = orders.length;
        orders[i] = newOrder;
        
        createOrderCard($form.parent(), i);
    });
    
    //delete completed order card
    $("div.orderList").on("click", "button.deleteOrderCard", function(e){
        e.stopPropagation();
        e.preventDefault();
        var i = $(this).attr("name");
        var $orderCardDiv = $(this).parent();
        $orderCardDiv.remove();
        orders[i] = null;
    });
    
    //submit survey
    $("#submitSurvey").click(function (e) {
        e.stopPropagation();
        e.preventDefault();

        var siteSelect = $("select[name=site]").val();
        var sitePw = $("input[name=sitePw]").val();
        var circle = $("select[name=circle]").val();
        var survey = $("select[name=survey]").val();
        var temperature = $("select[name=temperature]").val();
        var siteNotes = $("textarea[name=siteNotes]").val();
        var plantSpecies = $("input[name=plant]").val();
        
        var herbivory = $("#herbivory li").index($(".ui-selected"));
        var date = $("input[name=date]").val();
        var time = $("input[name=time]").val();
        

        //Check if a site is chosen and if a site password is entered
        if (!siteSelect) {
            $("p.error").remove();
            $("#submitSurvey").before("<p class = 'error'>Please choose a site.</p>");
            return;
        }

        if (!sitePw) {
            $("p.error").remove();
            $("#submitSurvey").before("<p class = 'error'>Please enter the site password.</p>");
            return;
        } 
        
        //Check if the rest of the required fields are filled
                if (!date) {
                    $("p.error").remove();
                    $("#submitSurvey").before("<p class = 'error'>Please enter a date.</p>");
                    return;
                }
                if (!time) {
                    $("p.error").remove();
                    $("#submitSurvey").before("<p class = 'error'>Please enter a time</p>");
                    return;
                }
                if (!temperature) {
                    $("p.error").remove();
                    $("#submitSurvey").before("<p class = 'error'>Please select a temperature</p>");
                    return;
                }
                if (!circle) {
                    $("p.error").remove();
                    $("#submitSurvey").before("<p class = 'error'>Please select a circle</p>");
                    return;
                }
                if (!survey) {
                    $("p.error").remove();
                    $("#submitSurvey").before("<p class = 'error'>Please select a survey</p>");
                    return;
                }
                if (!$("#herbivory .ui-selected").length) {
                    $("p.error").remove();
                    $("#submitSurvey").before("<p class = 'error'>Please select a herbivory score</p>");
                    return;
                }       

        //Check validity of site password
        var sitePwData = {
            action: "checkSitePassword",
            siteID: siteSelect,
            sitePasswordCheck: sitePw
        };

        $.ajax({
            url: baseUrl + "sites.php",
            type: "post",
            dataType: "json",
            data: JSON.stringify(sitePwData),
            success: function (data, xhr, status) {
                if (!data || !data.validSitePassword) {
                    $("p.error").remove();
                    $("#submitSurvey").before("<p class = 'error'>Site Password Incorrect.</p>");
                    return;
                }
                
                //site password correct

                
                
                var inputData = {
                    type: "survey",
                    userID: user.userID,
                    siteID: siteSelect,
                    timeStart: date + " " + time + ":00",
                    circle: circle,
                    survey: survey,
                    temperatureMin: temperature,
                    temperatureMax: parseInt(temperature) + 10,
                    siteNotes: siteNotes,
                    plantSpecies: plantSpecies,
                    herbivory: herbivory
                };
                
                $.ajax({
                    url: baseUrl + 'submission_full.php',
                    type: "post",
                    dataType: 'json',
                    data: JSON.stringify(inputData),
                    success: function (data, xhr, status) {
                        var surveyID = data.surveyID;
                        //now submit the orders
                        submitOrders(surveyID);
                        $("p.error").remove();
                        resetForm();
                        alert("Successfully submit survey with surveyID = " + data.surveyID);
                    },
                    error: function (xhr, status) {
                        alert("Error submitting the survey. Please try again.");
                    }
                });
            },
            error: function (xhr, status) {
                $("p.error").remove();
                $("#submitSurvey").before("<p class = 'error'>Site Password Incorrect.</p>");
            }
        });
    });
});

//create a new order form
function createNewOrder(){
    $form = $("<form class='order'></form>");
    $form.append("<pre>Arthropod: </pre><select class='arthropod'><option value=''>Please choose arthropod order</option>\n\
<option>Ants (Formicidae)</option><option>Aphids and Psyllids (Sternorrhyncha)</option>\n\
<option>Bees and Wasps (Hymenoptera, excluding ants)</option><option>Beetles (Coleoptera)</option>\n\
<option>Caterpillars (Lepidoptera larvae)</option><option>Daddy longlegs (Opiliones)</option>\n\
<option>Flies (Diptera)</option><option>Grasshoppers, Crickets (Orthoptera)</option>\n\
<option>Leaf hoppers and Cicadas (Auchenorrhyncha)</option><option>Moths, Butterflies (Lepidoptera)</option>\n\
<option>Spiders (Araneae; NOT daddy longlegs!)</option><option>True Bugs (Heteroptera)</option>\n\
<option>OTHER (describe in Notes)</option><option>UNIDENTIFIED (describe in Notes)</option>\n\
<option>NONE</option></select><br>");
    $form.append("<pre>Length:    </pre><input type='text' class='length'><br>");
    $form.append("<pre>Count:     </pre><input type='text' class='count'><br>");
    $form.append("<pre>Notes:     </pre><input type='text' class='orderNotes'><br>");
    $form.append("<button class='addOrderCard'>Add</button>");
    $form.append("<button class='cancel'>Cancel</button>");
    return $("<div class='order'></div>").append($form);
}

//create a completed order card
function createOrderCard($div, i){
    var order = orders[i];
    $div.empty();
    $div.append("<pre>Arthropod: " + order['arthropod'] + "</pre><br>");
    $div.append("<pre>Length:    " + order['length'] + "</pre><br>");
    $div.append("<pre>Count:     " + order['count']+ "</pre><br>");
    $div.append("<pre>Notes:     " + order['notes']+ "</pre><br>");
    $div.append("<button class ='deleteOrderCard' name=" + i + ">Delete Order</button>");
}

function submitOrders(surveyID){
    for(var i = 0; i < orders.length; i++){
        //skip null values(deleted order cards)in orders array
        if(!orders[i]) continue;
        
        var inputData = {
            type: "order",
            userID: user.userID,
            surveyID: surveyID,
            orderArthropod: orders[i].arthropod,
            orderLength: orders[i].length,
            orderNotes: orders[i].notes,
            orderCount: orders[i].count
        }
        
        $.ajax({
           url: baseUrl + "submission_full.php",
           type: "post",
           dataType: "json",
           data: JSON.stringify(inputData),
           success: function(data, xhr, status){
               
           },
           error: function(xhr, status){
               alert("Error submitting orders. Please try again.");
           }
        });
    }
}

function resetForm(){
    $("input[name=time]").val((new Date()).toTimeString().substring(0, "00:00".length));
    $("select[name=circle]").prop("selectedIndex", -1);  
    $("select[name=survey]").prop("selectedIndex", -1);    
    $("#herbivory .ui-selected").removeClass("ui-selected");
    $("input[name=plant]").val("");
    clearOrders();
}

function clearOrders(){
    orders = [];
    $("div.orderList").empty();
}