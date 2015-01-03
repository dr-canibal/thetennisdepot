var LiveChatPeriodUpdater = null;

String.prototype.trim = function()
{
    return this.replace(/(?:^\s+|\s+$)/g, "");
}

function SendMessage()
{
	var customermessage = document.getElementById('textmessage').value.trim();
	document.getElementById('textmessage').value = '';
	if (customermessage != '')
	{
		var img = document.getElementById('livachat_ajax_loader');
		if (img != null)
		{
			img.style.display = 'inline';
		}
		var request = new Ajax.Updater(
			'livechat_messages',
			urlSendMessage,
			{
			    method: 'get',
			    parameters: { message: customermessage },
			    onSuccess: function(transport, json) {
			        var img = document.getElementById('livechat_ajax_loader');
			        if (img != null) {
			            img.style.display = 'none';
			        }
			        SetUpdater();
			    },
			    insertion: Insertion.Bottom
			}
		);
	}
}

function SetUpdater()
{
	//alert(LiveChatPeriodUpdater);
	if (LiveChatPeriodUpdater == null)
	{
		LiveChatPeriodUpdater = new Ajax.PeriodicalUpdater(
								'livechat_messages',
								urlUpdater,
								{
									frequency: frequency,
									decay: decay
								}
							);
	}
}

function LiveChatKeyPress(e) {
    if (e.keyCode == 13) {
        SendMessage();
    }
}