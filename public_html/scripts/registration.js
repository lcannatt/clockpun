'use strict';

(function(){
	var recentQuery=false;
	var pending=false;
	var validated={
		username:false,
		password:false,
		password2:false,
		fname:false,
		lname:false,
		email:false
	}
	function inputFeedback(queryString,state,message){
		//creates feedback element in third column of table containing the input described with querystring.
		//state: 0=>failure,1=>success,2=>working
		let cssClass=['.failure','.success','.working']
		let unInput=document.querySelector(queryString);
		if(!unInput){
			console.log("Error finding "+queryString)
			return false;
		}
		let parent=unInput.closest("tr");
		let oldMsg=parent.querySelector(".formFeedback")
		if(oldMsg){
			oldMsg.parentElement.removeChild(oldMsg);
		}
		let feedback=TPR_GEN.newElement('td',{'className':'formFeedback '+cssClass[state],'innerText':message});
		parent.appendChild(feedback);
		
		
	}
	function validateUserNameOkHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		let input=document.querySelector("#username");
		if(input && input.value==obj.username){
			inputFeedback('#username',1,'Looks Good!');
			validated.username=true;
		}
	}
	function validateUserNameFailureHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		let input=document.querySelector("#username");
		if(input && input.value==obj.error){
			inputFeedback('#username',0,'Username is already taken');
			validated.username=false;
		}
	}
	function errorHandler(xhttp){
		CP_POPUP.makePopup('Sorry, there appears to be an error with the server. Please try again later.','Server Error');
	}

	function timeManagedValidation(){
		
		function resetQuery(){
			recentQuery=false;
			if(pending){
				timeManagedValidation();
				pending=false;
			}
		}
		if(recentQuery){
			inputFeedback('#username',2,'Working...');
			pending=true;
		}else{
			recentQuery=true;
			TPR_GEN.postWrapper(
				document.getElementById('reg-form'),
				validateUserNameOkHandler,
				validateUserNameFailureHandler,
				errorHandler,
				false
				);
			setTimeout(resetQuery,1000);
		}
	}
	
	function validateUserName(){
		let input=document.getElementById('username');
		let justAN= /^[a-zA-z0-9]+$/;
		if(input.value==''){
			validated.username=false;
			inputFeedback('#username',0,'Required');
		}else if(input.value.length<5){
			validated.username=false;
			inputFeedback('#username',0,'Must be at least 5 characters.');
		}else if(input.value.length>25){
			validated.username=false;
			inputFeedback('#username',0,'Must be less than 25 characters.');
		}else if(!justAN.test(input.value)){
			validated.username=false;
			inputFeedback('#username',0,'Only letters and numbers please.');
		}else{//its ok to send to the db wew
			timeManagedValidation();
		}
	}
	function validatePassword(){
		let input=document.getElementById('password');
		if(input.value==''){
			validated.password=false;
			inputFeedback('#password',0,'Required');
		}else if(input.value.length<7){
			validated.password=false;
			inputFeedback('#password',0,'Must be at least 7 characters.');
		}else{
			validated.password=true;
			inputFeedback('#password',1,"You're doing great.");
		}
		validatePasswordMatch();
	}
	function validatePasswordMatch(){
		let input=document.getElementById('password2');
		let password=document.getElementById('password');
		if(input.value==''){
			validated.password2=false;
			inputFeedback('#password2',0,'Required');
		}else if(input.value!=password.value){
			validated.password2=false;
			inputFeedback('#password2',0,"Passwords don't match, keep trying.");
		}else{
			validated.password2=true;
			inputFeedback('#password2',1,"Huzzah!");
		}
	}
	function validateName(id){
		let input=document.getElementById(id);
		if(input.value==''){
			validated[id]=false
			inputFeedback('#'+id,0,'Required');
		}else if(input.value.length<1){
			validated[id]=false
			inputFeedback('#'+id,0,"I need more characters than that")
		}else{
			validated[id]=true
			inputFeedback('#'+id,1,"EZ-PZ");
		}
	}
	function validateEmail(){
		let input=document.getElementById('email');
		if(input.value==''){
			validated.email=false;
			inputFeedback('#email',0,'Required');
		}else if(input.value.length<1){
			validated.email=false;
			inputFeedback('#email',0,"A valid email if you dont mind")
		}else{
			validated.email=true;
			inputFeedback('#email',1,"Look how far you've come!");
		}
	}
	function submitManager(){
		let register=document.getElementById('register');
		console.log(register,validated);
		if(!register){
			return false;
		}
		for(var property in validated){
			console.log(validated[property]);
			if(!validated[property]){
				register.disabled=true;
				return false;
			}
		}
		register.disabled=false;
	}
	document.addEventListener('keyup',function(e){
		let target=e.target;
		if(target.id=='username'){
			validateUserName();
		}
		if(target.id=='password'){
			validatePassword();
		}
		if(target.id=='password2'){
			validatePasswordMatch();
		}
		if(target.id=='fname'){
			validateName('fname');
		}
		if(target.id=='lname'){
			validateName('lname');
		}
		if(target.id=='username'){
			validateEmail();
		}
		submitManager();
	});
	CP_POPUP.initPopupHandler();
})();