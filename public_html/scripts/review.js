'use strict';
(function(){
    var bars=document.querySelectorAll('.day .hour-bar');
    bars.forEach(
        function(e){
            let hours=parseInt(e.getAttribute('value'));
            let width=(5*hours/8)+'em';
            e.style.width=width;
            e.innerText="";
        }
    );
    var totalbars=document.querySelectorAll('.total .hour-bar');
    totalbars.forEach(
        function(e){
            let hours=parseInt(e.getAttribute('value'));
            let width=(5*hours/40)+'em';
            e.style.width=width;
            e.innerText="";
        }
    );
})();