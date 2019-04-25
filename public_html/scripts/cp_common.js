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

	function makePopup(msg,title,style=2){
		// popup for user
		// type input: 0: Error, 1: OK, 2:Info
		let types=['error','ok','info']
		let popup=TPR_GEN.newElement('div',{'id':'popup','class':types[style]})
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
		makePopup : function(msg,title,style){
			return makePopup(msg,title,style);
		},
		emphasisePopup : function(){
			return emphasisePopup();
		}
	}

}();