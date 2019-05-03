'use strict';
(function(){
    var bars=document.querySelectorAll('.day .hour-bar');
    bars.forEach(
        function(e){
            let hours=parseFloat(e.getAttribute('value'));
            let width=(5*hours/8)+'em';
            e.style.width=width;
            e.innerText="";
        }
    );
    var totalbars=document.querySelectorAll('.total .hour-bar');
    totalbars.forEach(
        function(e){
            let hours=parseFloat(e.getAttribute('value'));
            let width=(5*hours/40)+'em';
            e.style.width=width;
            e.innerText="";
        }
    );
    var allbars=document.querySelectorAll('.bar-container');
    allbars.forEach(
        function(e){
            var hours=0;
            let hourDivs=e.querySelectorAll('.hour-bar');
            hourDivs.forEach(function(x){
                hours+=parseFloat(x.getAttribute('value'));
            })
            hours=hours.toFixed(1);
            console.log(e);
            if(hours.charAt(hours.length-1)=="0"){
                hours=hours.substring(0,hours.length-2);
            }
            let hoverDiv=TPR_GEN.newElement('div',{"className":'hover-count','innerText':hours});
            let mark8=e.querySelector('.mark-8');
            e.insertBefore(hoverDiv,mark8);
        }
    )
})();