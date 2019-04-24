'use strict';

(function(){
	var newElement=TPR_GEN.newElement;
	
	function newUserSuccessHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		let link=newElement('a',{'href':obj.url,'target':'_blank','innerText':obj.url})
		let message=newElement('span',{'innerHTML':'Account Successfully Created. Send User to the following link to finish account creation:<br>'});
		message.appendChild(link);
		CP_POPUP.makePopup(message,'Success!')
	}
	function newUserErrorHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		CP_POPUP.makePopup(obj.error,'Error');
	}
	function genericErrorHandler(xhttp){
		console.log('oh FUCK');
	}

	//RUN AT LOAD
	TPR_TABS.initTabs();
	document.addEventListener("submit", function(e){
		console.log(e);
		if(e.target.closest("#new-user")){
			event.preventDefault();
			TPR_GEN.postWrapper(e.target,
				newUserSuccessHandler,
				newUserErrorHandler,
				genericErrorHandler,
				);
		}
	});
	

})();