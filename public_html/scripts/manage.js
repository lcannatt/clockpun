'use strict';
//This file manages the front end for user management.
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
	function genericFailureHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		CP_POPUP.makePopup(obj.error,'Error',0);
	}
	function genericErrorHandler(xhttp){
		console.log('oh FUCK');
	}
	//Browse User/edit Tools
	function editSingleSuccessHandler(xhttp){
		let obj=JSON.parse(xhttp.responseText);
		if(obj.edit){
			closeEditBox();
			CP_POPUP.makePopup('The user has been updated','Great Success',1);
		}
	}
	function toggleInative(){
		let button=document.getElementById('toggle-inactive');
		if(button.value=="Show Inactive"){
			button.value="Hide Inactive";
		}else{
			button.value="Show Inactive";
		}
		document.querySelectorAll('.user-inactive').forEach(
			function(e){
				e.classList.toggle('nodisplay');
			})
	}
	
	function editSingle(userId){//creates single user editor. more or less looks like user creation with a password reset
		closeEditBox()
		let editBox=document.querySelector("#edit-single");
		editBox.classList.remove('nodisplay');
		document.querySelector('.main .wrapper').classList.add('inactive');
		let form=editBox.querySelector('form');
		form.disabled='true';
		form.appendChild(newElement('input',{'name':'userid','type':'hidden','value':userId}))
		let tempForm=newElement('form',{'action':'./pull','method':'POST'})
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

	function resetPassword(){
		function resetOKHandler(xhttp){
			let obj=JSON.parse(xhttp.responseText);
			let link=newElement('a',{'href':obj.url,'target':'_blank','innerText':obj.url})
			let message=newElement('span',{'innerHTML':'Password Reset Successfully: Please send user to the following link to update their password:<br>'});
			message.appendChild(link);
			CP_POPUP.makePopup(message,'Success!',1)
		}
		let box=document.querySelector('.edit-box:not(.nodisplay)');
		if(box){
			let userID=box.querySelector('input[name="userid"]');
			let form=TPR_GEN.newElement('form',{'action':'./reset-password','method':'POST'});
			form.appendChild(userID.cloneNode());
			TPR_GEN.postWrapper(form,
				resetOKHandler,
				genericFailureHandler,
				genericErrorHandler,
				true);
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
				genericFailureHandler,
				genericErrorHandler,
				true);
		}else if(e.target.closest("#edit-user")){
			event.preventDefault();
			TPR_GEN.postWrapper(e.target,
				editSingleSuccessHandler,
				genericFailureHandler,
				genericErrorHandler,
				true)
		}
	});
	document.addEventListener("click",function(e){
		console.log(e);
		//Edit users
		if(e.target.closest('#user-table') && e.target.closest('tr')){
			let input=e.target.closest('tr').querySelector('input');
			if(input){
				let userId=input.name.split('edit_')[1];
				editSingle(userId);
			}
		}else if(e.target.classList.contains("edit-exit")){
			closeEditBox();
		}else if(e.target.id=="toggle-inactive"){
			toggleInative();
		}else if(e.target.name=="resetpw"){
			resetPassword();
		}
	});
	

})();