const c=function(n,e=null,t=!1){document.querySelectorAll(`.checkbox[name^='${n}']`).forEach(function(o){const l=o.closest("tr").style.display==="none";(l&&t||!l)&&(o.checked=e??!o.checked)})},i=function(n){return Array.from(document.querySelectorAll(`[name^='${n}']`)).filter(e=>e.checked).length>0},s=function(n){let e=document.getElementById(n);if(e!=null){e.classList.remove("hidden"),e.classList.add("loading","loading-spinner");let t=e.parentElement;t!=null&&t.classList.add("btn-disabled")}},d=function(n){const e=document.querySelector(".job-"+n).querySelector('.worker-contract:not([style*="display: none;"])')!=null;return Alpine.store("show"+n+"main",e),e};window.toggleCheckBoxes=c;window.isAnyChecked=i;window.spin=s;window.toggleProjectVisibility=d;