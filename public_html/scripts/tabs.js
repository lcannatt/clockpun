'use strict';

(function main(){
	//Manage Tabs
	function initTabs(){
		//hide them all
		let tabs=document.querySelectorAll(".tab-contents");
		tabs.forEach(function(element){
			element.style.display="none";
		});
		//show only the one that's supposed to be active.
		let active=document.querySelector(".tabLink.active");
		console.log(active);
		if(active){
			let name=active.getAttribute('name');
			let tab=document.getElementById(name);
			if(tab){
				tab.style.display="block";
			}
		}
	}
	function activateTab(id){
		let tabs=document.querySelectorAll(".tab-contents");
		tabs.forEach(function(element){
			element.style.display="none";
		});
		//show only the one that's supposed to be active.
		let active=document.getElementById(id);
		if(active){
			active.style.display="block";
		}
		//update class tracking of who's active here
		document.querySelector(".tabLink.active").classList.toggle('active');
		document.querySelector('[name="'+id+'"]').classList.toggle('active');
	}

	//function calls to actually run
	initTabs();
	document.addEventListener("click",function(e){
		if(e.target.classList.contains("tabLink")){
			if(!e.target.classList.contains("addNew")){
				activateTab(e.target.getAttribute('name'));
			}
		}
	})
})();