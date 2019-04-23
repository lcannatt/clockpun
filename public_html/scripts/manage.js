'use strict';

(function(){
	function newElement(tagname,properties){
		// takes a tagname and json object and returns a new element with all those properties. 
		// NO ERROR CHECKING
		let elem=document.createElement(tagname);
		for(var property in properties){
			elem[property]=properties[property];
		}
		console.log(elem);
		return elem;
	}
	function emphasisePopup(){
		let popup=document.querySelector('#popup');
		if(popup){
			popup.style.backgroundColor="lightgrey";
			setTimeout(deemphasisePopup,100)
		}
	}
	function deemphasisePopup(){
		let popup=document.querySelector('#popup');
		if(popup){
			popup.removeAttribute('style');
		}
	}

	function makePopup(msg,title){
		//generic popup for user making
		let popup=newElement('div',{'id':'popup'})
		let header=newElement('div',{'id':'popup-header'});
		let titleSpan=newElement('span',{'id':'popup-title','innerText':title});
		header.appendChild(titleSpan);
		let exit=newElement('div',{'id':'popup-exit','innerText':'[ X ]'});
		header.appendChild(exit);
		popup.appendChild(header)
		let message=newElement('div',{'id':'popup-message'})
		if(typeof(msg)=="object"){
			message.appendChild(msg);
		}else{
			message.innerText=msg;
		}
		popup.appendChild(message);
		document.querySelector('.main').appendChild(popup);
		document.querySelector('.main .wrapper').classList.add('inactive');
		
	}
	function newUserSuccessHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		let link=newElement('a',{'href':obj.url,'target':'_blank','innerText':obj.url})
		let message=newElement('span',{'innerHTML':'Account Successfully Created. Send User to the following link to finish account creation:<br>'});
		message.appendChild(link);
		makePopup(message,'Success!')
	}
	function newUserErrorHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		makePopup(obj.error,'Error');
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
				genericErrorHandler
				);
		}
	});
	document.addEventListener('click',function(e){
		console.log(e);
		if(e.target.id=='popup-exit'){
			let popup=document.querySelector('#popup');
			if(popup){
				popup.parentElement.removeChild(popup);
				document.querySelector('.main .wrapper').classList.remove('inactive');
			}
		}else if(document.querySelector('#popup-exit')
					&&e.target.closest('.main')
					&&!e.target.closest('#popup')){
			emphasisePopup();
		}else if(e.target.classList.contains("tabLink")){
			if(!e.target.classList.contains("addNew")){
				TPR_TABS.activateTab(e.target.getAttribute('name'));
			}
		}
	});

})();