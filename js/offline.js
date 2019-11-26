

jQuery( document ).ready(function() {

   var lostInternetMessage = "<span class=\"imperial-feedback imperial-feedback-error\"><i class=\"fas fa-exclamation-triangle\"></i> Your device is no longer connected to the internet.</span><br/><span class=\"alertText\">We've disabled saving until this is fixed.<br/>But don't worry! No data will be lost if you reconnect and then submit this form.</span>";
   var foundInternetMessage = "<span class=\"imperial-feedback imperial-feedback-success\"><i class=\"fas fa-wifi\"></i> Your device is now back online!</span>";

   var currentStatus = '';

   var ifConnected = window.navigator.onLine;
     if (ifConnected) {
       //document.getElementById("checkOnline").innerHTML = "Online";
       //document.getElementById("checkOnline").style.color = "green";
       currentStatus = 'online';
     } else {
       document.getElementById("checkOnline").innerHTML = lostInternetMessage;
       jQuery("#agreedMarkingSubmitButton").hide();
       currentStatus = 'offline';


     }

   setInterval(function(){

   var ifConnected = window.navigator.onLine;
     if (ifConnected) {
        if(currentStatus=="offline")
        {
          currentStatus = "online";
          document.getElementById("checkOnline").innerHTML = foundInternetMessage;
          jQuery("#agreedMarkingSubmitButton").show();
          setInterval(function(){
             jQuery("#checkOnline").fadeOut("slow");
          }, 3000);
       }

     } else {

        if(currentStatus=="online")
        {
         // Hide the submit button
      //   jquery(#agreedMarkingSubmitButton).hide();
         jQuery("#agreedMarkingSubmitButton").hide();
          document.getElementById("checkOnline").innerHTML = lostInternetMessage;
          currentStatus = 'offline';
       }

     }

  }, 1000);


});
