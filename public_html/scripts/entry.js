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
		let table=document.getElementById('time-history');
		if(!table){
			console.log("Error updating table, document missing expected node");
		}
		for (let timeRow in obj) {
			if (obj.hasOwnProperty(timeRow)) {
				table.appendChild(createRow(obj[timeRow]));
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
	function dateChangeFailHandler(xhttp){
		let obj=JSON.parse(xhttp.responseText);
		CP_POPUP.makePopup(obj.error,"Error",0);
	}
	function genericErrorHandler(xhttp){
		CP_POPUP.makePopup('Error communicating with server, please check your internet connection','Error',0)
	}
	function updateManager(){
		let test=new Date(dateInput);
		let form=TPR_GEN.newElement('form',{'action':'/get-user-time','method':'POST'});
		form.appendChild(TPR_GEN.newElement('input',{'name':'date','value':dateInput.value}));
		TPR_GEN.postWrapper(form,
			dateChangeOkHandler,
			dateChangeFailHandler,
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
	
	function newTime(){
		function newTimeOKHandler(xhttp){
			let obj= JSON.parse(xhttp.responseText);
			let form=document.getElementById('edit-time');
			if(form){
				form.reset();
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':obj.id,'name':'timeID'}))
				form.classList.remove('nodisplay');
			}
			let newTime=document.getElementById('new-time');
			if(newTime){
				newTime.classList.add('nodisplay');
			}
		}
		TPR_GEN.getWrapper('/new-time',
			newTimeOKHandler,
			dateChangeFailHandler,
			genericErrorHandler
		)
	}
	function editTime(id){
		function getTimeHandler(xhttp){
			let obj=JSON.parse(xhttp.responseText);
			let form=document.getElementById('edit-time');
			if(form){
				form.reset();
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':obj.id,'name':'timeID'}))
				form.classList.remove('nodisplay');
			}
			document.getElementById('start').value=obj.time_start;
			let newTime=document.getElementById('new-time');
			if(newTime){
				newTime.classList.add('nodisplay');
			}
		}
		let form=TPR_GEN.newElement('form',{'action':'/get-time','method':'POST'});
		form.appendChild(TPR_GEN.newElement('input',{'name':'id','value':id}));
		TPR_GEN.postWrapper(form,
			getTimeHandler,
			dateChangeFailHandler,
			genericErrorHandler,
			true);
	}
	function saveTime(){
		console.log('saving time');
	}
	function deleteTime(){
		console.log('deleting time');
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
	});
	//Handle requests to server for new time input id on Add New Entry click


	CP_POPUP.initPopupHandler();
})();