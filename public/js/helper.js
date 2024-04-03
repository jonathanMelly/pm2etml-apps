//adapted from https://stackoverflow.com/questions/61470556/how-to-check-and-uncheck-all-checkboxes-by-clicking-one-checkbox-using-alpine-js
toggleCheckBoxes = function (name,force=null,onlyHidden=false) {
    document.querySelectorAll(`.checkbox[name^='${name}']`).forEach(
        function (el)
        {
            hidden = el.closest('tr').style.display==='none'
            if((hidden && onlyHidden) || !hidden) {
                el.checked = (force == null ?
                    !el.checked :
                    force);
            }
        });

}

isAnyChecked = function (name)
{
    return Array.from(document.querySelectorAll(`[name^='${name}']`)).filter(el=>el.checked).length>0;
}

spin = function(target){
    let el = document.getElementById(target);
    if(el!=null){
        el.classList.remove("hidden");
        el.classList.add("loading","loading-spinner");
        let parent = el.parentElement;
        if(parent!=null){
            parent.classList.add("btn-disabled")
        }
    }
}

function toggleProjectVisibility(jobId)
{
    const hasChildren = document.querySelector('.job-'+jobId).querySelector(`.worker-contract:not([style*="display: none;"])`)!=null;
    Alpine.store('show'+jobId+'main',hasChildren);
    return hasChildren;
}


