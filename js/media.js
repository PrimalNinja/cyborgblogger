function media(objAPI_a, objParameters_a) 
{
    var api = objAPI_a;
    var m_objParameters = objParameters_a;
    var m_strContainerID = m_objParameters.containerID;
    var m_objGallery = null;
	var m_strGalleryID = '';
    
	// Generate unique GUID for this instance
	function getGUID()
	{
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(strChar_a) 
		{
			var intR = Math.random() * 16 | 0;
			var intV = strChar_a === 'x' ? intR : (intR & 0x3 | 0x8);
			return intV.toString(16);
		});
	}

	function initMedia()
	{
		// Initialize login handlers
		initLoginHandlers();
		
		// Initialize gallery
		initGallery();
	}
    
    function initLoginHandlers() 
	{
        var arrLoginLinks = api.element('#' + m_strContainerID, m_objParameters.loginLink);
        
		arrLoginLinks.on('click', function(objEvent_a) 
		{
            objEvent_a.preventDefault();
            var objBox = api.element('#' + m_strContainerID, m_objParameters.loginBox);
            if (objBox.length > 0) 
			{
                objBox.show();
                var objInput = api.element('#' + m_strContainerID, m_objParameters.adminField);
                if (objInput.length > 0) 
				{
                    objInput.focus();
                }
            }
        });
    }
    
    function initGallery() 
	{
		var intI;
		var objWindow;
		
		// Generate unique ID and assign it to the gallery container
		m_strGalleryID = 'gallery-' + getGUID();
		var objGalleryContainer = api.element('#' + m_strContainerID, m_objParameters.gallery);
		objGalleryContainer.attr('id', m_strGalleryID);
		
        m_objGallery = new listRenderer(api, m_strGalleryID, {
            dataSource: 'local',
            type: 'GALLERY',
            flow: 'EXPAND',
            target: '',
            thumbsize: { X: 150, Y: 150 },
            imagefield: 'IMAGEURL',
            initialData: m_objParameters.mediaData,
			cbLoadData: function(objRequest_a, callback_a)
			{
				// Parse the request parameters
				var objParams = {};
				for (intI = 0; intI < objRequest_a.parameters.length; intI++) {
					var param = objRequest_a.parameters[intI];
					objParams[param.name] = param.value;
				}
				
				var strSearch = objParams.search ? objParams.search.toLowerCase() : '';
				
				// Get the original media data
				var objOriginalData = m_objParameters.mediaData;
				var arrAllData = JSON.parse(JSON.stringify(objOriginalData.data)); // Deep clone
				
				// Filter based on description (if search is not empty)
				var arrFiltered = arrAllData;
				if (strSearch.length > 0) {
					arrFiltered = [];
					for (intI = 0; intI < arrAllData.length; intI++) {
						var arrRow = arrAllData[intI];
						// Generate description same way as cbGetDescription
						var strDesc = arrRow[1] + '\n' + arrRow[3].toUpperCase() + ' (' + arrRow[4] + ' KB)';
						
						// Check if search term is found in description (case-insensitive)
						if (strDesc.toLowerCase().indexOf(strSearch) !== -1) {
							arrFiltered.push(arrRow);
						}
					}
				}
				
				// Call the callback with filtered response
				callback_a({
					control: { 
						offset: 0, 
						limit: arrFiltered.length, 
						total: arrFiltered.length,
						more: false 
					},
					operations: objOriginalData.operations,
					fields: objOriginalData.fields,
					data: arrFiltered
				});
			},
            cbGetDescription: function(arrRow) 
			{
                return arrRow[1] + '\n' + arrRow[3].toUpperCase() + ' (' + arrRow[4] + ' KB)';
            },
            cbOnClick: function(arrRow, strID) 
			{
                objWindow = api.getWindow();
                if (objWindow.console && objWindow.console.log) 
				{
                    objWindow.console.log('Click: ' + strID);
                }
            },
            cbOnDblClick: function(arrRow, strID) 
			{
                // Open file in new tab
                objWindow = api.getWindow();
                objWindow.open(m_objParameters.mediaDir + arrRow[1], '_blank');
            },
            cbOnOperation: function(strOperation, arrRow, strID) 
			{
                if (strOperation === 'delete') 
				{
                    objWindow = api.getWindow();
                    if (objWindow.confirm('Delete file: ' + arrRow[1] + '?')) 
					{
                        var objForm = api.createElement('form');
                        objForm.method = 'POST';
                        objForm.action = 'media.php';
                        objForm.className = 'ge-media-delete-form';
                        
                        var objInput1 = api.createElement('input');
                        objInput1.type = 'hidden';
                        objInput1.name = 'delete_file';
                        objInput1.value = '1';
                        objForm.appendChild(objInput1);
                        
                        var objInput2 = api.createElement('input');
                        objInput2.type = 'hidden';
                        objInput2.name = 'filename';
                        objInput2.value = arrRow[1];
                        objForm.appendChild(objInput2);
                        
                        api.element('#' + m_strContainerID).append(objForm);
                        objForm.submit();
                    }
                } 
				else if (strOperation === 'copy_url') 
				{
                    objWindow = api.getWindow();
                    var strURL = objWindow.location.origin + '/' + m_objParameters.mediaDir + arrRow[1];
                    
                    if (objWindow.navigator && objWindow.navigator.clipboard) 
					{
                        objWindow.navigator.clipboard.writeText(strURL).then(function() 
						{
                            objWindow.alert('URL copied to clipboard:\n' + strURL);
                        });
                    }
                } 
				else if (strOperation === 'view') 
				{
                    objWindow = api.getWindow();
                    objWindow.open(m_objParameters.mediaDir + arrRow[1], '_blank');
                }
            }
        });
        
        objGalleryContainer.html(m_objGallery.render());
    }
	
	initMedia();
}