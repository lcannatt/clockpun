var CP_POPUP = function(){

	function emphasisePopup(){
		let popup=document.querySelector('#popup');
		if(popup){
			popup.classList.add("emphasis");
			setTimeout(deemphasisePopup,100)
		}
	}
	function deemphasisePopup(){
		let popup=document.querySelector('#popup');
		if(popup){
			popup.classList.remove('style');
		}
	}

	function makePopup(msg,title){
		//generic popup for user making
		let popup=TPR_GEN.newElement('div',{'id':'popup'})
		let header=TPR_GEN.newElement('div',{'id':'popup-header'});
		let titleSpan=TPR_GEN.newElement('span',{'id':'popup-title','innerText':title});
		header.appendChild(titleSpan);
		let exit=TPR_GEN.newElement('div',{'id':'popup-exit','innerText':'[ X ]'});
		header.appendChild(exit);
		popup.appendChild(header)
		let message=TPR_GEN.newElement('div',{'id':'popup-message'})
		if(typeof(msg)=="object"){
			message.appendChild(msg);
		}else{
			message.innerText=msg;
		}
		popup.appendChild(message);
		document.querySelector('.main').appendChild(popup);
		//Block out clicks in the main content area until the popup is closed
		document.querySelector('.main .wrapper').classList.add('inactive');
		
	}
	function initPopupHandler(){
		document.addEventListener("click",function(e){
			if(e.target.id=='popup-exit' || !e.target.closest('#popup')){
				//delete the popup if it's closed or clicked out of.
				let popup=document.querySelector('#popup');
				if(popup){
					popup.parentElement.removeChild(popup);
					document.querySelector('.main .wrapper').classList.remove('inactive');
				}
			}
		});
	}
	return {
		initPopupHandler : function(){
			return initPopupHandler();
		},
		makePopup : function(msg,title){
			return makePopup(msg,title);
		},
		emphasisePopup : function(){
			return emphasisePopup();
		}
	}

}();