/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**************************************************!*\
  !*** ./resources/js/pages/form-advanced.init.js ***!
  \**************************************************/

document.addEventListener('DOMContentLoaded', function () {
  var genericExamples = document.querySelectorAll('[data-trigger]');

  for (i = 0; i < genericExamples.length; ++i) {
    var element = genericExamples[i];
    new Choices(element, {
      placeholderValue: 'This is a placeholder set in the config',
      searchPlaceholderValue: 'This is a search placeholder'
    });
  }

  var amenities =  new  Choices('#amenities',{
        searchEnabled: true,
        removeItemButton: true,
        duplicateItemsAllowed: true,
 });
  var food_menus =  new  Choices('#food_menus',{
        searchEnabled: true,
        removeItemButton: true,
        duplicateItemsAllowed: true,
  });

});
/******/ })()
;
