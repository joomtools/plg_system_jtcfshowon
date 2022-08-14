# Joomla! System - Plugin - JT CF Showon
[![Joomla 3](https://img.shields.io/badge/Joomla™-3.10-darkgreen?logo=joomla&logoColor=c2c9d6&style=for-the-badge)](https://downloads.joomla.org/cms) [![Joomla 4](https://img.shields.io/badge/Joomla™-4.1-darkgreen?logo=joomla&logoColor=c2c9d6&style=for-the-badge)](https://downloads.joomla.org/cms)  
![PHP5.6](https://img.shields.io/badge/PHP-5.6-darkgreen?logo=php&style=for-the-badge) ![PHP7.0](https://img.shields.io/badge/PHP-7.0-darkgreen?logo=php&style=for-the-badge) ![PHP7.1](https://img.shields.io/badge/PHP-7.1-darkgreen?logo=php&style=for-the-badge) ![PHP7.2](https://img.shields.io/badge/PHP-7.2-darkgreen?logo=php&style=for-the-badge) ![PHP7.4](https://img.shields.io/badge/PHP-7.4-darkgreen?logo=php&style=for-the-badge) ![PHP8.0](https://img.shields.io/badge/PHP-8.0-darkgreen?logo=php&style=for-the-badge) ![PHP8.1](https://img.shields.io/badge/PHP-8.1-darkgreen?logo=php&style=for-the-badge)  


## Anleitung / Manual
<details>
  <summary>Deutsch / German</summary>

## Deutsche Anleitung
<p>Das Plugin <strong>JT - Showon</strong> erweitert Customfields um die Möglichkeit, sie in Abhängigkeit von anderen Feldern darzustellen.</p>
<p><strong>Eine Außnahme bildet das SubformFeld ab, hier funktioniert das Showon nur auf das SubformFeld selber und nicht auf die darin verwendeten Felder.</strong></p>
<p>Zuerst muss das Plugin <a href="https://github.com/JoomTools/plg_system_jtcfshowon/releases/latest">hier</a> heruntergeladen, installiert und aktiviert werden.</p>
<p>Bei der Erstellung eines Customsfields erscheint daraufhin ein neues Eingabefeld "Showon".<br/>
Dort wird als erstes der Name des Feldes eingegeben, von dem unser Feld abhängig sein soll. Durch einen Doppelpunkt getrennt wird der Wert des Feldes eingegeben. z.B. <strong>eltern-feld:1</strong><br/>
Unser neues Feld erscheint nur, wenn das eltern-feld den Wert 1 hat.</p>
<p>Es können verschiedenen Felder oder Werte verknüpft werden:<br/>
<strong>Mehrere Felder müssen zutreffen:</strong>
Verknüpfung mit [AND].<br/>
Beispeil: <strong>eltern-feldA:1[AND]eltern-feldB:1</strong><br>
<strong>Eines der Felder muss zutreffen:</strong>
Verknüpfung mit [OR].<br/>
Beispeil: <strong>eltern-feldA:1[OR]eltern-feldB:1</strong></p>
<p>Wichtig hierbei, [AND] und [OR] sollten immer in Großbuchstaben und ohne Leerzeichen, davor oder danach, verwendet werden.</p>
<p>Eine vollständige Erklärung der Funktionsweise kann in der <a href="https://docs.joomla.org/Form_field/de#Showon" target="_blank">Joomla! Dokumentation</a> nachgelesen werden.</p>
<p><strong>Author:</strong> Guido De Gobbis<br/><strong>Copyright:</strong> © <a href="https://github.com/JoomTools" target="_blank">JoomTools.de</a><br/><strong>Plugin-Lizenz:</strong> <a href="https:/www.gnu.org/licenses/gpl-3.0.de.html" target="_blank">GNU/GPLv3</a><br/><strong>Plugin-Version:</strong> <a href="https://github.com/JoomTools/plg_system_jtcfshowon/releases">herunterladen</a></p>
</details>

<details>
  <summary>Englisch / English</summary>

## English Manual
<p>The plugin <strong>JT - Showon</strong> extends the customfields with a new functionality to show the field in dependence of of another customfield.</p>
<p><strong>An exception is the subform field, here the showon only works on the subform field itself and not on the fields used in it.</strong></p>
<p>First you have to <a href="https://github.com/JoomTools/plg_system_jtcfshowon/releases/latest">download</a>, install and aktivate the plugin.</p>
<p>In the custimfields settings you have a new formfield "showon".<br/>
First you type the name of your customfield the field should depend on. Separated by a colon you write the value. f.e. parent-field:1<br/>
Our new field only appears if the parent-field has the value 1.</p>
<p>You can link several fields an values:<br/>
<strong>all fields have to be true:</strong>
link with [AND].<br/>
Example: parent-fieldA:1[AND]parent-fieldB:1<br>
<strong>One field have to be true:</strong>
Link with [OR].<br/>
Example: parent-fieldA:1[OR]parent-fieldB:1</p>
<p>Important here, [AND] and [OR] should always be used in capital letters and without spaces before or after them.</p>
<p>A full explanation of how it works can be found in the <a href="https://docs.joomla.org/Form_field#Showon" target="_blank">Joomla! documentation</a>.</p>
<p><strong>Author:</strong> Guido De Gobbis<br/><strong>Copyright:</strong> © <a href="https://github.com/JoomTools" target="_blank">JoomTools.de</a><br/><strong>Plugin licens:</strong> <a href="https:/www.gnu.org/licenses/gpl-3.0.en.html" target="_blank">GNU/GPLv3</a><br/><strong>Download</strong> <a href="https://github.com/JoomTools/plg_content_jteasylink/releases/latest">latest Version</a></p>
</details>
