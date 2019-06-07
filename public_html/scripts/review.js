'use strict';
//BIG TO DOS:
//MAKE THE CORRECT BUTTONS PAGE DEPENDENT
//KEEP DAILY AND WEEKLY TOTAL DISPLAYS UPDATED WITH AVAILABLE DATA.
(function(){
    //IMPORTANT GLOBALS::
    //EDIT MODE?
    var editMode=window.location.pathname.match(/^\/hr$/);
    //MAIN EDITBOX
    var editForm=document.getElementById('edit-time')
    var contents=document.querySelector('.tab-contents.active')
    //DATE,USER
    var user;
    var date;
    //GUI HANDLING
    function widthInit(){//Calculates the correct widths for all time bars displayed on page and applies
        var bars=document.querySelectorAll('.bar-container.day');
        function calculateWidth(barElem,cutoff,sum){
            let hours=parseFloat(barElem.getAttribute('value'));
            let width=(5*hours/cutoff);
            if(width+sum>=6){
                width=6-sum;
            }
            sum=sum+width;
            width=width+'em';
            barElem.style.width=width;
            barElem.innerText="";
            return sum;
        }
        bars.forEach(
            function(e){
                let peices=e.querySelectorAll('.hour-bar');
                let total=0;
                peices.forEach((element)=>{total=calculateWidth(element,8,total);});                
            }
        );
        var totalbars=document.querySelectorAll('.total .hour-bar');
        totalbars.forEach((element)=>{calculateWidth(element,8,0);});
        var allbars=document.querySelectorAll('.bar-container');
        allbars.forEach(
            function(e){
                var hours=0;
                let hourDivs=e.querySelectorAll('.hour-bar');
                hourDivs.forEach(function(x){
                    hours+=parseFloat(x.getAttribute('value'));
                })
                hours=hours.toFixed(1);
                if(hours.charAt(hours.length-1)=="0"){
                    hours=hours.substring(0,hours.length-2);
                }
                let hoverDiv=TPR_GEN.newElement('div',{"className":'hover-count','innerText':hours});
                let mark8=e.querySelector('.mark-8');
                e.insertBefore(hoverDiv,mark8);
            }
        )
    }

    //DETAILS
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
    function genericFailureHandler(xhttp){
		let obj=JSON.parse(xhttp.responseText);
		CP_POPUP.makePopup(obj.error,"Error",0);
	}
	function genericErrorHandler(xhttp){
		CP_POPUP.makePopup('Error communicating with server, please check your internet connection','Error',0)
    }
    
	function updateEntries(){
		let form=TPR_GEN.newElement('form',{'action':'./get-user-time','method':'POST'});
        form.appendChild(TPR_GEN.newElement('input',{'name':'date','value':date}));
        form.appendChild(TPR_GEN.newElement('input',{'name':'user','value':user}));
		TPR_GEN.postWrapper(form,
			dateChangeOkHandler,
			genericFailureHandler,
			genericErrorHandler,
			false);

		function dateChangeOkHandler(xhttp){
			let obj = JSON.parse(xhttp.responseText);
			//For now we're just going to delete and re-add every time there's a change.
			//To do: implement cacheing of loaded time data.
			//First clear out the old table
            let old=document.querySelector('#time-details');
            if(old){
                old.parentElement.removeChild(old)
            };
            //now build a new table
            let table=TPR_GEN.newElement('table',{'className':'interactive-table','id':'time-history'});
            let tbody=TPR_GEN.newElement('tbody',{});
            tbody.appendChild(createHeader());
			for (let timeRow in obj) {
				if (obj.hasOwnProperty(timeRow)) {
					tbody.appendChild(createRow(obj[timeRow]));
				}
            }
            table.appendChild(tbody);

            //Finally, locate where this thing is supposed to go, and append it.
            let insertionRow=document.getElementById(user);
            let newRow=TPR_GEN.newElement('tr',{'id':'time-details'});
            let td=TPR_GEN.newElement('td',{});
            td.setAttribute('colspan',insertionRow.childElementCount-2);
            let container=TPR_GEN.newElement('div',{'className':'expander'});
            if(editMode){
                let addTime=TPR_GEN.newElement('input',{'type':'button','value':'Add New Entry','id':'new-time'})
                let floater=TPR_GEN.newElement('div',{'className':'float-r'})
                floater.appendChild(addTime);
                container.appendChild(floater);
            }
            container.appendChild(table);
            td.appendChild(container);
            newRow.appendChild(TPR_GEN.newElement('td',{'innerText':date}));
            newRow.appendChild(td);
            insertionRow.parentElement.insertBefore(newRow,insertionRow.nextSibling);
            container.style.height=table.scrollHeight+'px';
            
			function createRow(data){
				//creates a time table row from one id 
				let row=TPR_GEN.newElement('tr',{});
				row.appendChild(TPR_GEN.newElement('input',{'type':'hidden','name':'timeID','value':data.time_id}));
				let elapsed;
				let minutes;
				if(data.elapsed!=null){
					let elapsedText=minToTime(data.elapsed);
					elapsed=TPR_GEN.newElement('td',{'innerText':elapsedText})
					minutes=data.elapsed;
				}else{
					let startTime=Date.parse(date+" "+data.start)
					let diff=Date.now()-startTime;
					minutes=~~(diff/(1000*60));
					let timer=TPR_GEN.newElement('span',{'value':startTime,'innerText':minToTime(minutes),'className':'timer'});
					elapsed=TPR_GEN.newElement('td',{});
					elapsed.appendChild(timer);
				}
				row.appendChild(TPR_GEN.newElement('input',{'type':'hidden','name':'minutes','value':minutes}))
				let timeText=data.start+' - '+(data.end?data.end:'Timer Running');
				row.appendChild(TPR_GEN.newElement('td',{'innerText':timeText}));
				row.appendChild(elapsed);
				row.appendChild(TPR_GEN.newElement('td',{'innerText':data.cat_name}));
				row.appendChild(TPR_GEN.newElement('td',{'innerText':data.comment}));
				return row;
            }
            function createHeader(){
                let tr=TPR_GEN.newElement('tr',{'className':'header-row'});
                tr.appendChild(TPR_GEN.newElement('th',{'innerText':'Time'}))
                tr.appendChild(TPR_GEN.newElement('th',{'innerText':'Hours'}))
                tr.appendChild(TPR_GEN.newElement('th',{'innerText':'Category'}))
                tr.appendChild(TPR_GEN.newElement('th',{'innerText':'Comment'}))
                return tr;
            }
		}

    }
    //TIME EDITING
    function reset(form){//General purpose form clearing tool
        contents.appendChild(form)
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
    function placeForm(form){
        let newLoc=document.querySelector('#time-history');
        newLoc.parentElement.appendChild(form);
        form.classList.remove('nodisplay');
        newLoc.parentElement.style.height=(newLoc.scrollHeight+form.scrollHeight)+'px';
    }
	function newTime(date){//Get time entry id from db, initialize the time details
		function newTimeOKHandler(xhttp){
			let obj= JSON.parse(xhttp.responseText);
			let form=document.getElementById('edit-time');
			if(form){
				reset(form);
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':obj.id,'name':'timeID'}))
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':date,'name':'date'}))
				placeForm(form);
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
	function editTime(id,date){
		function getTimeOKHandler(xhttp){
			let obj=JSON.parse(xhttp.responseText);
			let form=document.getElementById('edit-time');
			if(form){
				reset(form);
				form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':obj.time_id,'name':'timeID'}))
                form.appendChild(TPR_GEN.newElement('input',{'type':'hidden','value':date,'name':'date'}))
                placeForm(form)
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
			getTimeOKHandler,
			genericFailureHandler,
			genericErrorHandler,
			true);
	}
	function saveTime(){
		let form=editForm
		function saveTimeOKHandler(xhttp){
			let obj=JSON.parse(xhttp.responseText);
			let localID=form.querySelector('input[name="timeID"]').value;
			if(obj.id==localID){
				closeEdit(form);
                updateEntries();
                updateDayBar()
                widthInit();
			}else{
				CP_POPUP.makePopup('A synchronization error occurred, please try again','Error',0);
			}
		}
		TPR_GEN.postWrapper(form,
			saveTimeOKHandler,
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
				updateEntries();
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
            let date=e.target.closest('#time-details').querySelector('td').innerText;
			newTime(date);
		}
		if(e.target.id=="save"){
			saveTime();
		}
		if(e.target.id=="delete"){
			deleteTime();
		}
		if(e.target.closest("#time-history tr")){
            let date=e.target.closest('#time-details').querySelector('td').innerText;
			let id=e.target.closest("tr").querySelector('input[name="timeID"]').value;
			editTime(id,date);
		}
		if(e.target.id=='startNow'){
			timeToNow('start');
		}
		if(e.target.id=='endNow'){
			timeToNow('end');
        }
        if(e.target.closest('.bar-container.day')){//details expansion
            date=e.target.closest('.bar-container.day').getAttribute('date');
            user=e.target.closest('tr').id;
            if(editForm){reset(editForm);}
            updateEntries();
        }
    });
    
    widthInit();
    CP_POPUP.initPopupHandler();
})();