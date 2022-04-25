//adapted from https://stackoverflow.com/questions/61470556/how-to-check-and-uncheck-all-checkboxes-by-clicking-one-checkbox-using-alpine-js
toggleCheckBoxes = function (name,force=null) {
    document.getElementsByName(name).forEach(el=>el.checked=force==null?
        !el.checked:
        force);
}

isAnyChecked = function (name)
{
    return Array.from(document.getElementsByName(name)).filter(el=>el.checked).length>0;
}


