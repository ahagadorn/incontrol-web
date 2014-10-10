incontrol-web
=============

inControl HA Web Interface

This project is intended to eventually be a functional clone of the inControl HA Windows interface using PHP and jquery. It is 100% ajax dynamic HTML that updates every 5 seconds. 

INSTALLATION

  1. Install and configure PHP and a suitable web server such as apache
  2. Unzip incontrol-web-<version>.zip in the web server HTML directory
  3. Make sure the web server user can write to the directory (necessary for the log file)
  4. Edit config.php to point to your inControl HA server and enter your password
  5. Browse to your web server URL, you should see all your devices.

USAGE

  Click on the device icon to turn the device on/off, there is no Power button like on the InControl interface.
  
  Click on a device anywhere except the icon to select it for display in the device information box on the right. The information
  box will update every 5 seconds, along with all the devices in the device box except for thermostats. This is because thermostats
  can be controlled from the information box, and updating the information box can interfere with changing the settings. 
  The thermostat device in the devices box will continue to update, and the thermostat information box can be updated by clicking
  on the device in the devices box. 
