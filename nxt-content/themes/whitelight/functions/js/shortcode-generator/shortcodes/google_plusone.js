lokShortcodeMeta={
	attributes:[
		{
			label:"Optional URL to +1",
			id:"href",
			help:"Optionally place the URL you want viewers to '+1' here. Defaults to the page/post URL."
		}, 
		{
			label:"Size",
			id:"size",
			help:"Values: standard, small, medium, tall (default: standard).<p>Note: Depending on how fast the Google +1 API is today, the preview could take a few moments to load.</p>",
			controlType:"select-control", 
			selectValues:['standard', 'small', 'medium', 'tall'],
			defaultValue: 'standard', 
			defaultText: 'standard (Default)'
		},  
		{
			label:"Float",
			id:"float",
			help:"Float left, right, or none.",
			controlType:"select-control", 
			selectValues:['', 'left', 'right'],
			defaultValue: '', 
			defaultText: 'none (Default)'
		}, 
		{
			label:"Annotation",
			id:"annotation",
			help:"Optionally show the counter of users who '+1' your URL, either as inline text or in a bubble.",
			controlType:"select-control", 
			selectValues:['none', 'bubble', 'inline'],
			defaultValue: 'none', 
			defaultText: 'none (Default)'
		}, 
		{
			label:"Language",
			id:"language",
			help:"Select the language in which to display the button.",
			controlType:"select-control", 
			selectValues:[  'Arabic', 
							'Bulgarian', 
							'Catalan', 
							'Chinese (Simplified)', 
							'Chinese (Traditional)', 
							'Croatian', 
							'Czech', 
							'Danish', 
							'Dutch', 
							'English (US)', 
							'English (UK)', 
							'Estonian', 
							'Filipino', 
							'Finnish', 
							'French', 
							'German', 
							'Greek', 
							'Hebrew', 
							'Hindi', 
							'Hungarian', 
							'Indonesian', 
							'Italian', 
							'Japanese', 
							'Korean', 
							'Latvian', 
							'Lithuanian', 
							'Malay', 
							'Norwegian', 
							'Persian', 
							'Polish', 
							'Portuguese (Brazil)', 
							'Portuguese (Portugal)', 
							'Romanian', 
							'Russian', 
							'Serbian', 
							'Swedish', 
							'Slovak', 
							'Slovenian', 
							'Spanish', 
							'Spanish (Latin America)', 
							'Thai', 
							'Turkish', 
							'Ukrainian', 
							'Vietnamese'
						],
			defaultValue: 'English (UK)', 
			defaultText: 'English (UK) (Default)'
		}, 
		{
			label:"JavaScript Callback Function",
			id:"callback",
			help:"Optionally include a JavaScript callback function to run when the +1 button is clicked. <strong>For Advanced Users Only</strong>."
		} 
		],
		defaultContent:"",
		shortcode:"google_plusone"
};