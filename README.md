# Script to control TV and Firewall Functions from a Web page

This is a very simple script to illustrate how you can control a switch
interface with SNMP and pfSense firewall rules using fauxapi 

Get yourself a copy of ndejong's pfsense fauxapi package for pfsense:

[https://github.com/ndejong/pfsense_fauxapi]https://github.com/ndejong/pfsense_fauxapi

To use for pfsense, follow the instructions to install fauxapi on your pfsense
firewall and then replace the appropriate values in the script.

To use for just snmp interface configuration, make sure your version of php
supports snmp and set up a read/write community on the router/switch to use
then apply the appropriate values to the variables in the Vars section of the
script.

Please feel free to modify as you see fit and use it for whatever purpose you
need.  Realize that there is no authentication on this, it is mainly here as a
very basic proof of concept, you may want to set this up behind an htaccess 
resource on your webserver or add an authentication element to it so not just 
anyone can enable/disable things on your switch and firewall.


# License 

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
