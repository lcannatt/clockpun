'use strict';

(function(){
	var newElement=TPR_GEN.newElement;
	//New User tools
	function newUserSuccessHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		let link=newElement('a',{'href':obj.url,'target':'_blank','innerText':obj.url})
		let message=newElement('span',{'innerHTML':'Account Successfully Created. Send User to the following link to finish account creation:<br>'});
		message.appendChild(link);
		CP_POPUP.makePopup(message,'Success!',1)
	}
	function newUserErrorHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		CP_POPUP.makePopup(obj.error,'Error',0);
	}
	function genericErrorHandler(xhttp){
		console.log('oh FUCK');
	}
	//Browse User/edit Tools
	
	
	function editSingle(userId){//creates single user editor. more or less looks like user creation with a password reset
		closeEditBox()
		let editBox=document.querySelector("#edit-single");
		editBox.classList.remove('nodisplay');
		document.querySelector('.main .wrapper').classList.add('inactive');
		let form=editBox.querySelector('form');
		form.disabled='true';
		form.appendChild(newElement('input',{'name':'userid','type':'hidden','value':userId}))
		let tempForm=newElement('form',{'action':'/pull','method':'POST'})
		tempForm.appendChild(newElement('input',{'name':'userid','value':userId}))
		function pullOKHandler(xhttp){
			let obj = JSON.parse(xhttp.responseText);
			//TO DO UPDATE THE FORM VALUES
			form.querySelector('[name="fname"]').value=obj.first_name
			form.querySelector('[name="lname"]').value=obj.last_name
			form.querySelector('[name="email"]').value=obj.email
			form.querySelector('[name="manager"]').value=obj.boss_id
			form.querySelectorAll('[name="grant[]"]').forEach(
				function(current){
					if(obj.flags.hasOwnProperty(current.value)&&obj.flags[current.value]==1){
						current.checked=true;
					}
				}
			);
			return true;
		}
		function pullFailHandler(xhttp){
			let obj = JSON.parse(xhttp.responseText);
			closeEditBox();
			CP_POPUP.makePopup('Error: '+obj.error,'Error',0);
		}
		TPR_GEN.postWrapper(tempForm,
			pullOKHandler,
			pullFailHandler,
			genericErrorHandler,
			false)
		
	}
	function editMulti(userIdAry){
		closeEditBox();
		let editBox=document.querySelector("#edit-multi");
		editBox.classList.remove("nodisplay");
		document.querySelector('.main .wrapper').classList.add('inactive');
		let form=editBox.querySelector('form');
		for(let id in userIdAry){
			form.appendChild(newElement('input',{'type':'hidden','name':'userIds[]','value':userIdAry[id]}));
		}
		
	}
	function closeEditBox(){
		let box=document.querySelector('.edit-box:not(.nodisplay)');
		if(box){
			box.querySelectorAll('input[type="hidden"]').forEach(
				function(elem){
					elem.parentElement.removeChild(elem);
				}
			);
			box.querySelector('form').reset();
			box.classList.add('nodisplay');
			document.querySelector('.main .wrapper').classList.remove('inactive');
		}
	}



	//RUN AT LOAD
	TPR_TABS.initTabs();
	CP_POPUP.initPopupHandler();
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
	document.addEventListener("click",function(e){
		console.log(e);
		//browse edit button
		if(e.target.closest('input[type="button"]') && e.target.value=='Edit'){
			let checkedUsers=e.target.closest('.tab-contents').querySelectorAll('input[type="checkbox"]:checked');
			if(checkedUsers.length>1){
				let ids=[];
				checkedUsers.forEach(function(checkbox){
					ids.push(checkbox.name.split("edit_")[1])
				});
				editMulti(ids);
			}else if(checkedUsers.length>0){
				let id=checkedUsers[0].name.split("edit_")[1];
				editSingle(id);
			}
		}else if(e.target.classList.contains("edit-exit") ||
		(document.querySelector(".edit-box:not(.nodisplay)") && !e.target.closest(".edit-box"))){
			closeEditBox();
		}
	});
	

})();