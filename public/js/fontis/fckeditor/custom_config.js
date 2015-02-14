/*--------------- Insert custom toolbar set definitions here ----------------*/

// Example 'medium' toolbar set - more than Basic but less than Default.
FCKConfig.ToolbarSets["Medium"] = [
['Source', '-', 'Cut','Copy','Paste','PasteText','PasteWord'],
['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
'/',
['Bold','Italic','Underline','StrikeThrough','-', 'OrderedList','UnorderedList'],
['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
['Image','Table','Rule','-','Link','Unlink','Anchor','-','FontFormat']
];

/*----------------- End of custom toolbar set definitions -------------------*/



/*-------------- Other FCKEditor Magento compatibility settings -------------*/
FCKConfig.ProcessHTMLEntities = false;
FCKConfig.FormatSource = false;
FCKConfig.FormatOutput = false;
