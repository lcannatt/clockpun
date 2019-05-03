'use strict';
(function(){
	//Change which day's time is display automatically when the date selection is changed
	var dateInput=document.getElementById('date');
	function minToTime(minutes){
		let hr=~~(minutes/60);
		let min=(minutes%60);
		return hr+':'+(min>9?min:"0"+min);
	}
	/**
	 * Get the number of days in any particular month
	 * @link https://stackoverflow.com/a/1433119/1293256
	 * @param  {integer} m The month (valid: 0-11)
	 * @param  {integer} y The year
	 * @return {integer}   The number of days in the month
	 */
	function daysInMonth(m, y) {
		switch (m) {
			case 1 :
				return (y % 4 == 0 && y % 100) || y % 400 == 0 ? 29 : 28;
			case 8 : case 3 : case 5 : case 10 :
				return 30;
			default :
				return 31
		}
	}
	function hrsMinFromDate(date){
		return (date.getHours()<10?'0':'')+date.getHours()+":"+(date.getMinutes()<10?'0':'')+date.getMinutes();
	}
	/**
	 * Check if a date is valid
	 * @link https://stackoverflow.com/a/1433119/1293256
	 * @param  {[type]}  d The day
	 * @param  {[type]}  m The month
	 * @param  {[type]}  y The year
	 * @return {Boolean}   Returns true if valid
	 */
	function isValidDate (datestring) {
		let [y,m,d] = datestring.split('-');
		m = parseInt(m, 10) - 1;
		return m >= 0 && m < 12 && d > 0 && d <= daysInMonth(m, y);
	}
	function dateChangeOkHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		//For now we're just going to delete and re-add every time there's a change.
		//To do: implement cacheing of loaded time data.
		//First clear out the old data rows
		document.querySelectorAll('#time-history tr:not(.header-row)').forEach(
			function(e){
				e.parentElement.removeChild(e);
			}
		);
		//now add the new data
		let tbody=document.querySelector('#time-history tbody');
		if(!tbody){
			console.log("Error updating table, document missing expected node");
		}
		for (let timeRow in obj) {
			if (obj.hasOwnProperty(timeRow)) {
				tbody.appendChild(createRow(obj[timeRow]));
			}
		}
		function createRow(data){
			//creates a time table row from one id 
			let row=TPR_GEN.newElement('tr',{});
			row.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':data.time_id}));
			let timeText=data.start+' - '+(data.end?data.end:'Timer Running');
			row.appendChild(TPR_GEN.newElement('td',{'innerText':timeText}));
			let elapsed;
			if(data.elapsed!=null){
				let elapsedText=minToTime(data.elapsed);
				elapsed=TPR_GEN.newElement('td',{'innerText':elapsedText})
			}else{
				let startTime=Date.parse(dateInput.value+" "+data.start)
				let diff=Date.now()-startTime;
				let minutes=~~(diff/(1000*60));
				let timer=TPR_GEN.newElement('span',{'value':startTime,'innerText':minToTime(minutes),'className':'timer'});
				elapsed=TPR_GEN.newElement('td',{});
				elapsed.appendChild(timer);
			}
			row.appendChild(elapsed);
			row.appendChild(TPR_GEN.newElement('td',{'innerText':data.cat_name}));
			row.appendChild(TPR_GEN.newElement('td',{'innerText':data.comment}));
			return row;
		}
	}
	function genericFailureHandler(xhttp){
		let obj=JSON.parse(xhttp.responseText);
		CP_POPUP.makePopup(obj.error,"Error",0);
	}
	function genericErrorHandler(xhttp){
		CP_POPUP.makePopup('Error communicating with server, please check your internet connection','Error',0)
	}
	function updateManager(){
		let form=TPR_GEN.newElement('form',{'action':'./get-user-time','method':'POST'});
		form.appendChild(TPR_GEN.newElement('input',{'name':'date','value':dateInput.value}));
		TPR_GEN.postWrapper(form,
			dateChangeOkHandler,
			genericFailureHandler,
			genericErrorHandler,
			false);
	}
	document.addEventListener("change",function(e){
		if(e.target.id=='date'
			&& isValidDate(e.target.value)){
			updateManager();
		}
	});
	//Make the time edit form visible whenever a time needs to be edited, handle changing which one is being edited
	function reset(form){//General purpose form clearing tool
		form.reset();
		form.querySelectorAll('input[type="hidden"]').forEach(
			function(e){
				e.parentElement.removeChild(e);
			}
		);
	}
	function closeEdit(form){//Reset and hide the time details form, add back the new entry button
		reset(form);
		form.classList.add('nodisplay');
		document.getElementById('new-time').classList.remove('nodisplay');
	}
	function newTime(){//Get time entry id from db, initialize the time details
		function newTimeOKHandler(xhttp){
			let obj= JSON.parse(xhttp.responseText);
			let form=document.getElementById('edit-time');
			if(form){
				reset(form);
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':obj.id,'name':'timeID'}))
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':dateInput.value,'name':'date'}))
				form.classList.remove('nodisplay');
			}
			let newTime=document.getElementById('new-time');
			if(newTime){
				newTime.classList.add('nodisplay');
			}
		}
		TPR_GEN.getWrapper('./new-time',
			newTimeOKHandler,
			genericFailureHandler,
			genericErrorHandler
		)
	}
	function editTime(id){
		function getTimeHandler(xhttp){
			let obj=JSON.parse(xhttp.responseText);
			let form=document.getElementById('edit-time');
			if(form){
				reset(form);
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':obj.time_id,'name':'timeID'}))
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':dateInput.value,'name':'date'}))
				form.classList.remove('nodisplay');
			}
			//input the db values into the form
			//start time
			let startDate=new Date(obj.time_start);
			let valueString=hrsMinFromDate(startDate);
			document.getElementById('start').value=valueString;
			//end time
			if(obj.time_end){
				let endDate=new Date(obj.time_end);
				valueString=hrsMinFromDate(endDate);
				document.getElementById('end').value=valueString;
			}
			//category
			if(obj.category){
				document.getElementById('category').value=obj.category;
			}
			//comments
			if(obj.comment){
				document.getElementById('comments').value=obj.comment;
			}
			//hide the new entry button while edit is open
			let newTime=document.getElementById('new-time');
			if(newTime){
				newTime.classList.add('nodisplay');
			}
		}
		let form=TPR_GEN.newElement('form',{'action':'./get-time','method':'POST'});
		form.appendChild(TPR_GEN.newElement('input',{'name':'id','value':id}));
		TPR_GEN.postWrapper(form,
			getTimeHandler,
			genericFailureHandler,
			genericErrorHandler,
			true);
	}
	function saveTime(){
		var form=document.getElementById('edit-time');
		function saveTimeHandler(xhttp){
			let obj=JSON.parse(xhttp.responseText);
			let localID=form.querySelector('input[name="timeID"]').value;
			if(obj.id==localID){
				closeEdit(form);
				updateManager();
			}else{
				CP_POPUP.makePopup('A synchronization error occurred, please try again','Error',0);
			}
		}
		TPR_GEN.postWrapper(form,
			saveTimeHandler,
			genericFailureHandler,
			genericErrorHandler,
			true);
		console.log('saving time');
	}
	function deleteTime(){
		let form=TPR_GEN.newElement('form',{'action':'./delete-time','method':'POST'});
		var id=document.querySelector('#edit-time input[name="timeID"]');
		form.appendChild(id.cloneNode());

		function deleteOKHandler(xhttp){
			let obj=JSON.parse(xhttp.responseText);
			if(obj.time_id==id.value){
				let editForm=document.getElementById('edit-time')
				closeEdit(editForm);
				updateManager();
			}
		}
		TPR_GEN.postWrapper(form,
			deleteOKHandler,
			genericFailureHandler,
			genericErrorHandler,
			true);
	}
	function timeToNow(id){
		document.getElementById(id).value=hrsMinFromDate(new Date());
	}
	document.addEventListener("click",function(e){
		if(e.target.closest('#new-time')){
			newTime();
		}
		if(e.target.id=="save"){
			saveTime();
		}
		if(e.target.id=="delete"){
			deleteTime();
		}
		if(e.target.closest("#time-history tr")){
			let id=e.target.closest("tr").querySelector('input').value;
			editTime(id);
		}
		if(e.target.id=='startNow'){
			timeToNow('start');
		}
		if(e.target.id=='endNow'){
			timeToNow('end');
		}
	});
	updateManager();
	function timerAnimationLooper(colon){
		let activeClocks=document.querySelectorAll('.timer');
		activeClocks.forEach(function(e){
			let startTime=e.value
			let diff=Date.now()-startTime;
			let timecode=minToTime(~~(diff/(1000*60)));
			if(!colon){
				timecode=timecode.split(':').join(' ')
			}
			e.innerText=timecode;
		});
		let nextColon=!colon;
		setTimeout(timerAnimationLooper.bind(null,nextColon),1000);
	}
	timerAnimationLooper(true);
	CP_POPUP.initPopupHandler();
})();