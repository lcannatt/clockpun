'use strict';

var TPR_TABS = function main(){
	//Manage Tabs
	function initTabs(){
		//hide them all
		let tabs=document.querySelectorAll(".tab-contents");
		tabs.forEach(function(element){
			element.style.display="none";
		});
		//show only the one that's supposed to be active.
		//first check if we have one in the window hash\
		let active;
		if(window.location.hash.length>1){
			active=document.querySelector('[name="'+window.location.hash.substr(1)+'"]');
		}
		if(active==null){
			active=document.querySelector('.tabLink');
		}
		console.log(active);
		if(active){
			active.classList.toggle('active')
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
		window.location.hash='#'+id;
	}

	return{
		initTabs:function(){
			return initTabs();
		},
		activateTab:function(id){
			return activateTab(id);
		}
	}
}();