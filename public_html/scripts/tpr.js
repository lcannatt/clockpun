'use stict';

var TPR_GEN = function (){
	//	Generic Functions for use across the site

	//  Generic tpr AJAX post handler
	//  Behaviors: * adds ajax field to form and submits asynchonously.
	//             * disables the form that sent it. (prevent repeat submissions.)
	//             * binds onSuccess to http status code 200, X-Status header is 'ok' response event with xhttp object
	//             * binds onFailure to http status code 200, X-Status not 'ok' or non 200 error code event with xhttp object
	//             * binds onError to network level failure.
	//             * re-enables the form after a response/error of any kind is recieved.
	var postWrapper = function(form,onSuccess,onFailure,onError,disable=true){
		var xhttp= new XMLHttpRequest();
		var FD= new FormData(form);
		FD.append("async","1");
		xhttp.addEventListener("load",function(event){
			if(disable){
				form.querySelectorAll("input,textarea").forEach(function(currentValue){currentValue.disabled=false;});
			}
			if(xhttp.status==200){
				if(xhttp.getResponseHeader('X-status')=='ok'){
					onSuccess.bind(null,xhttp)();
				} else { // X-Status not set, most likely not meant for an async call
					onFailure.bind(null,xhttp)()
				}
			} else { // Non 200 code response
				onFailure.bind(null,xhttp)()
			}
			
		})
		xhttp.addEventListener("error",function(){
			if(disable){
				form.querySelectorAll("input,textarea").forEach(function(currentValue){currentValue.disabled=false;});
			}
			onError.bind(null,xhttp)();
			
		});
		xhttp.open("POST",form.action);
		xhttp.send(FD);
		if(disable){
			//Disable form submissions while waiting for response
			form.querySelectorAll("input,textarea").forEach(function(currentValue){currentValue.disabled=true});
		}
	}

	//  Generic tpr AJAX Get handler
	//  Behaviors: * Creates xhttp Get request to url and initiates it.
	//             * binds onSuccess to http status code 200 && Header: X-Status='ok event with xhttp object
	//             * binds onFailure to non 200 status code and Header: X-Status!='ok' response event with xhttp object
	//             * binds onError to network level failure.

	var getWrapper= function(url,onSuccess,onFailure,onError){
		var xhttp = new XMLHttpRequest();
		xhttp.addEventListener("load", function(){
			if(xhttp.status==200){
				if(xhttp.getResponseHeader('X-status')=='ok'){
					onSuccess.bind(null,xhttp)();
				} else { // X-Status not set, most likely not meant for an async call
					onFailure.bind(null,xhttp)()
				}
			} else { // Non 200 code response
				onFailure.bind(null,xhttp)()
			}
		});
		xhttp.addEventListener("error", onError.bind(null,xhttp));
		xhttp.open("GET", url, true);
		xhttp.send();
	}

	//  @MARK adding a cookie setter helper function
	//  using nonstandard word 'delta' for the time from d.getTime() from which the cookie will expire
	//  normally I'd be a little hesitant to remove generality from the user setting a cookie to expire whenever,
	//  instead of locking him down to a difference from right now, but this way is a lot easier for usage,
	//  and I can't think of when you'd want to set a specific datetime for cookie expiry as opposed to a delta.
	var setCookie = function(name, value, delta, path) {
		var d = new Date();
		d.setTime(d.getTime() + delta);
		document.cookie = name + "=" + value + ";" + "expires=" + d.toUTCString() + ";path=" + path;
	}

	//  GENERIC ELEMENT CREATION WRAPPER
	// 	Behavior: 	* Creates new element with tag name of @Param tagName
	//				* Copies all enumerable properties of @Param properties (json object) to new element
	//				* Returns element object.
	var newElement = function(tagName,properties){
		let elem=document.createElement(tagName);
		for(var property in properties){
			elem[property]=properties[property];
		}
		return elem;
	}

	return{
		postWrapper: function(form,onSuccess,onFailure,onError,disable){
			return postWrapper(form,onSuccess,onFailure,onError,disable);
		},
		getWrapper : function(url,onSuccess,onFailure,onError){
			return getWrapper(url,onSuccess,onFailure,onError);
		},
		setCookie : function(name, value, delta, path){
			return setCookie(name, value, delta, path);
		},
		newElement : function(tagName,properties){
			return newElement(tagName,properties);
		}
	}
}();

var TPR_TABS = function main(){
	//Manage Tabs
	function initTabs(){
		//hide them all
		// let tabs=document.querySelectorAll(".tab-contents");
		// tabs.forEach(function(element){
		// 	element.style.display="none";
		// });
		//show only the one that's supposed to be active.
		//first check if we have one in the window hash\
		let active;
		if(window.location.hash.length>1){
			active=document.querySelector('[name="'+window.location.hash.substr(1)+'"]');
		}
		if(active==null){
			active=document.querySelector('.tabLink');
		}
		if(active){
			active.classList.add('active');
			let name=active.getAttribute('name');
			let tab=document.getElementById(name);
			if(tab){
				tab.classList.add("active");
			}
		}
		//Set up event listener:
		document.addEventListener('click',function(e){
			if(e.target.classList.contains("tabLink")){
				let id=e.target.getAttribute('name')
				if(id){
					activateTab(id);
				}
				
			}
		});

	}
	function activateTab(id){
		let tabs=document.querySelectorAll(".tab-contents,.tabLink");
		tabs.forEach(function(element){
			element.classList.remove("active");
		});
		//show only the one that's supposed to be active.
		let active=document.getElementById(id);
		if(active){
			active.classList.add("active");
		}
		//update class tracking of who's active here
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