function blogger(objAPI_a, objParameters_a) 
{
    var api = objAPI_a;
    var m_objParameters = objParameters_a;
    var m_strContainerID = m_objParameters.containerID;
    
	function initBlogger()
	{
		// Initialize wiki content rendering
		initWikiContent();
		
		// Initialize login box handlers
		initLoginHandlers();
		
		// Initialize delete form handlers
		initDeleteHandlers();
	}
    
    function initWikiContent() 
	{
        var arrWikiElements = api.element('#' + m_strContainerID, '.wiki-content');
        
		for (var intI = 0; intI < arrWikiElements.length; intI++) 
		{
            var objElement = arrWikiElements[intI];
            var strWikiText = api.element(objElement).attr('data-wiki');
            if (strWikiText) 
			{
                // Call CyborgWiki function with empty parameters for internal links/images
                var strHtml = cyborgWikiToHtml(strWikiText, '', '', null);
                api.element(objElement).html(strHtml);
            }
        }
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
    
    function initDeleteHandlers() 
	{
        var arrDeleteForms = api.element('#' + m_strContainerID, m_objParameters.deleteForm);
        
		arrDeleteForms.on('submit', function(objEvent_a) 
		{
            // Just submit the form - browser handles it
            return true;
        });
    }
	
	initBlogger();
}