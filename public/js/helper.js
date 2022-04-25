/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!********************************!*\
  !*** ./resources/js/helper.js ***!
  \********************************/
//adapted from https://stackoverflow.com/questions/61470556/how-to-check-and-uncheck-all-checkboxes-by-clicking-one-checkbox-using-alpine-js
toggleCheckBoxes = function toggleCheckBoxes(name) {
  var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  document.getElementsByName(name).forEach(function (el) {
    return el.checked = force == null ? !el.checked : force;
  });
};

isAnyChecked = function isAnyChecked(name) {
  return Array.from(document.getElementsByName(name)).filter(function (el) {
    return el.checked;
  }).length > 0;
};
/******/ })()
;