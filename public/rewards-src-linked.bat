@echo off

pushd .  
%~d0     
cd %~dp0 

REM put this file in the Magento installation directory

REM point this to the path of all the Sweet Tooth files as an absolute path.

set source_path=C:\dev\prog\st

REM remove old links or files
rd  /s /q  .\app\code\community\TBT\Rewards
del /s /q  .\app\design\adminhtml\default\default\layout\rewards.xml
rd  /s /q  .\app\design\adminhtml\default\default\template\rewards
del /s /q  .\app\design\frontend\default\default\layout\rewards.xml
rd  /s /q  .\app\design\frontend\default\default\template\rewards
rd  /s /q  .\app\design\frontend\default\default_sweet_tooth_1.3
rd  /s /q  .\app\design\frontend\default\default_sweet_tooth_1.4.0.1
rd  /s /q  .\app\design\frontend\default\default_sweet_tooth_1.4.1.1
rd  /s /q  .\app\design\frontend\default\default_sweet_tooth_1.4.2.0
del /s /q  .\app\etc\modules\TBT_Rewards.xml
rd  /s /q  .\app\locale\en_US\template\email\rewards
rd  /s /q  .\js\tbt\rewards
rd  /s /q  .\js\tbt\scriptaculous
rd  /s /q  .\skin\frontend\default\default\css\rewards
del /s /q  .\skin\frontend\default\default\fonts\arial.ttf
rd  /s /q  .\skin\frontend\default\default\images\rewards  

rd  /s /q  .\skin\adminhtml\default\default\rewards

rd  /s /q  .\skin\frontend\base\default\css\rewards

REM rebuild directory struct  
md         .\app\code\community\TBT                            
md         .\app\design\adminhtml\default\default\layout\   
md         .\app\design\adminhtml\default\default\template     
md         .\app\design\frontend\default\default\layout\    
md         .\app\design\frontend\default\default\template      
md         .\app\design\frontend\default           
md         .\app\etc\modules\                       
md         .\app\locale\en_US\template\email                   
md         .\js\tbt
md         .\skin\frontend\default\default\css                 
md         .\skin\frontend\default\default\fonts\            
md         .\skin\frontend\default\default\images  
  
md         .\skin\adminhtml\default\default\
        
md         .\skin\frontend\base\default\css            

REM rebuild links                
mklink /D  .\app\code\community\TBT\Rewards                           %source_path%\app\code\community\TBT\Rewards
mklink     .\app\design\adminhtml\default\default\layout\rewards.xml  %source_path%\app\design\adminhtml\default\default\layout\rewards.xml
mklink /D  .\app\design\adminhtml\default\default\template\rewards    %source_path%\app\design\adminhtml\default\default\template\rewards
mklink     .\app\design\frontend\default\default\layout\rewards.xml   %source_path%\app\design\frontend\default\default\layout\rewards.xml
mklink /D  .\app\design\frontend\default\default\template\rewards     %source_path%\app\design\frontend\default\default\template\rewards

mklink /D  .\app\design\frontend\default\default_sweet_tooth_1.3      %source_path%\app\design\frontend\default\default_sweet_tooth_1.3
mklink /D  .\app\design\frontend\default\default_sweet_tooth_1.4.1.1  %source_path%\app\design\frontend\default\default_sweet_tooth_1.4.0.1
mklink /D  .\app\design\frontend\default\default_sweet_tooth_1.4.1.1  %source_path%\app\design\frontend\default\default_sweet_tooth_1.4.1.1
mklink /D  .\app\design\frontend\default\default_sweet_tooth_1.4.2.0  %source_path%\app\design\frontend\default\default_sweet_tooth_1.4.2.0

mklink     .\app\etc\modules\TBT_Rewards.xml                          %source_path%\app\etc\modules\TBT_Rewards.xml
mklink /D  .\app\locale\en_US\template\email\rewards                  %source_path%\app\locale\en_US\template\email\rewards
mklink /D  .\js\tbt\rewards                                           %source_path%\js\tbt\rewards
mklink /D  .\js\tbt\scriptaculous                                     %source_path%\js\tbt\scriptaculous
mklink /D  .\skin\frontend\default\default\css\rewards                %source_path%\skin\frontend\default\default\css\rewards
mklink     .\skin\frontend\default\default\fonts\arial.ttf            %source_path%\skin\frontend\default\default\fonts\arial.ttf
mklink /D  .\skin\frontend\default\default\images\rewards             %source_path%\skin\frontend\default\default\images\rewards    

mklink /D  .\skin\frontend\base\default\css\rewards             %source_path%\skin\frontend\base\default\css\rewards

mklink /D  .\skin\adminhtml\default\default\rewards             %source_path%\skin\adminhtml\default\default\rewards

popd           


pause
