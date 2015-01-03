<?php

class Beck_LiveChat_Block_Reporting extends Mage_Adminhtml_Block_Template
{
	public function getTransformationReporting()
	{
		$chats = Mage::getModel('livechat/archives_session')->getCollection()->load();
		$total_chat = $chats->count();
		$count_order = 0;
		foreach ($chats as $chat)
		{
			if ($chat->getOrder_placed() != '')
			{
				$count_order++;
			}
		}
		if ($total_chat <= 0)
		{
			$percent_order = 0;
			$percent_noorder = 100;
		}
		else
		{
			$percent_order = $count_order / $total_chat * 100;
			$percent_noorder = ($total_chat - $count_order) / $total_chat * 100;
		}
		$xml = "<chart>
	<chart_data>
		<row>
			<null/>
			<string>".$this->__('Chat with order')."</string>
			<string>".$this->__('Chat without order')."</string>
		</row>
		<row>
			<string></string>
			<number>".$percent_order."</number>
			<number>".$percent_noorder."</number>
		</row>
	</chart_data>
	<chart_grid_h thickness='0' />
	<chart_label shadow='low' color='000000' alpha='65' size='10' position='inside' as_percentage='true' />
	<chart_pref select='false' drag='true' rotation_x='60' min_x='20' max_x='90' />
	<chart_rect x='180' y='40' width='200' height='200' positive_alpha='0' />
	<chart_transition type='spin' delay='.5' duration='0.75' order='category' />
	<chart_type>3d pie</chart_type>
	<draw>
		<rect bevel='bg' layer='background' x='0' y='0' width='400' height='250' fill_color='4c5577' line_thickness='0' />
		<text shadow='low' color='0' alpha='10' size='40' x='0' y='260' width='500' height='50' v_align='middle'>567890123456789012</text>
		<rect shadow='low' layer='background' x='-50' y='70' width='500' height='200' rotation='-5' fill_alpha='0' line_thickness='80' line_alpha='5' line_color='0' />
	</draw>
	<filter>
		<shadow id='low' distance='2' angle='45' color='0' alpha='50' blurX='4' blurY='4' />
		<bevel id='bg' angle='180' blurX='100' blurY='100' distance='50' highlightAlpha='0' shadowAlpha='15' type='inner' />
		<bevel id='bevel1' angle='45' blurX='5' blurY='5' distance='1' highlightAlpha='25' highlightColor='ffffff' shadowAlpha='50' type='inner' />
	</filter>
	<legend bevel='bevel1' transition='dissolve' delay='0' duration='1' x='0' y='45' width='50' height='210' margin='10' fill_color='0' fill_alpha='20' line_color='000000' line_alpha='0' line_thickness='0' layout='horizontal' bullet='circle' size='12' color='ffffff' alpha='85' />
	<series_color>
		<color>00ff88</color>
		<color>ffaa00</color>
	</series_color>
</chart>";
		$xml = ereg_replace("\n", '', $xml);
		$xml = ereg_replace("\r", '', $xml);
		$xml = ereg_replace("\t", '', $xml);
		return $xml;
	}
	
	public function getChart($xml_data, $width = 400, $height = 250)
	{
		$flash_url = $this->getSkinUrl('media/livechat/charts.swf');
		$library_path = $this->getSkinUrl('media/livechat/charts_library/');
		$flash = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,45,0" width="'.$width.'" height="'.$height.'">
					<param name="movie" value="'.$flash_url.'">
					<param name="uality" value="high">
					<param name="wmode" value="transparent">
					<param name="flashvars" value="library_path='.$library_path.'&xml_data='.$xml_data.'">
					<embed src="'.$flash_url.'" flashvars="library_path='.$library_path.'&xml_data='.$xml_data.'" quality="high" wmode="transparent" width="'.$width.'" height="'.$height.'" name="tech" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash pluginspage="http://www.macromedia.com/go/getflashplayer" />
				</object>';
		return $flash;
	}
}