<?php
	/* /modules/form/form.php
	 * Autor: Handle Marco
	 * Version: 0.4.0
	 * Beschreibung:
	 *	Funktionen für Formulare
	 */
/*

//Das Formular wird wie folgt beschrieben:
			name		Text			Typ			size_a	size_b	value		sonstiges	
$fields = array(
	array( "std", 		"Stunde(n): ", 	"text", 	"15",	"",		"test",		"readonly=\"true\""),
	array( "datum", 	"Datum: ", 		"textarea", "10",	"10",	"",	""),
	array( "klasse", 	"Klasse: ", 	"checkbox", "",		"",		"true",	""),
	array( "lehrer", 	"Lehrer: ", 	"checkbox", "",		"",		"",			""),
	array( "fach", 		"Fach: ", 		"button", 	"",		"",		"",			""),
	array( "check", 	"Ok", 			"dropdown",	"",		"",		$select,	""),		
	);

*/

include_once(ROOT_LOCATION . "/modules/form/HashGenerator.php");
/*Funktion um die Formulare zu erstellen
 *
 *$field - Array das die Informationen über das Formular enthält als string 
 *$content- Inhalt des Formulars wenn leeres Formular false sonst array mit Inhalten
 *
 */
function form_new($field,$content,$hashGenerator)
{	

$allowedSites = array('substitudes','absentees','news');


printf("<table>\n");	//Tabellen Tag auf
	printf("<tr>\n");	//Zeilen 	Tag auf
  		printf("<form method=\"post\">\n");	//Formular Anfang Tag
  			//Für jeden Eintrag im Array field einmal diese Schleife durchlaufen
  			$hashGenerator->printForm();
foreach($field as $f) {	
	//Wenn Content mitgeliefert wird und der Typ des aktuellen Inputs kein DropDown, dann Content austauschen
	if($content!=false){
		//Content des aktuellen Inputs austauschen, Name des Array-Indizes ist der name des Inputs
		$f[5]=$content[$f[0]]; 
	}
	
	if ($f[2] == "checkbox") { //Checkbox
		//Wenn gecheckt, dann Variable checked auf checked setzen
		if($f[5]==true){
			$checked="checked";
		}
		else{ //sonst leer
			$checked="";					
		}
		
		printf("<td>%s <input type=\"checkbox\" name=\"%s\" %s value=\"%s\" %s>\n", //Checkbox erstellen
			 $f[1], $f[0], $checked, $f[0],$f[6]);
	}
	else if($f[2] == "text") {
		printf("<td>%s <input type=\"text\" name=\"%s\" size=\"%spx\" value=\"%s\" %s >\n", //Textbox erstellen
				$f[1], $f[0], $f[3], $f[5], $f[6]);
	}
	else if($f[2] == "date") {
		printf("<td>%s <input type=\"date\" name=\"%s\" size=\"%spx\" value=\"%s\" %s >\n", //Textbox erstellen
				$f[1], $f[0], $f[3], $f[5], $f[6]);
	}
	else if($f[2] == "textarea") {
		printf("<td>%s <textarea name=\"%s\" cols=\"%s\" rows=\"%s\" %s>%s</textarea>\n", //Textarea erstellen
				$f[1], $f[0], $f[3], $f[4], $f[6], $f[5]);
	}
	else if($f[2] == "button") {
		printf("<td><input type=\"submit\" name=\"%s\" value=\"%s\" style=\"width: %spx; height: %spx\" %s>\n",	//Button erstellen
				$f[0], $f[1], $f[3], $f[4], $f[6]);
	}
	else if($f[2] == "hidden") {
		printf("<td><input type=\"hidden\" name=\"%s\" value=\"%s\">\n", //Versteckten Typ erstellen
				$f[0], $f[5]);
	}
	else if($f[2] == "dropdown") { //Dropdown Menü erstellen
		printf("<td>%s <input value=\"%s\" autocomplete=\"off\" size=\"%s\" list=\"%s\" name=\"%s\" %s></td>\n",
				$f[1], $f[5], $f[3], $f[0], $f[0], $f[6]);			
	}
}
			
$temp = explode("/", $_SERVER['REQUEST_URI']);	//URL Zerlegen
			
if($content==false)
{
	form_savedelete(true);
}
else if(array_intersect($temp,$allowedSites)!=array())
{
	form_savedelete(false);
}
else
{
	form_savedelete(true);
}

				
		printf("</form>\n");
  	printf("</tr>\n");
printf("</table>\n");
}

function form_lesson($field,$content,$hashGenerator)
{	
$zeile=0;
printf("<table>\n");	//Tabellen Tag auf
do{
if(($zeile)%7==0){
	$zeile1=1;
	$hour=(($zeile)/7)+1;
	$visibility="style=\"display:tabel-row\"";
}
else{
	$zeile1=0;
	$hour=floor((($zeile)/7)+1);
	$visibility="style=\"display:none\"";
}

$rowID="id=\"visibleRow".($zeile+1-(($hour-1)*7)).$hour."\"";
$stuff=array('split'=>1,'length'=>1);	
	
if($content!=""){
	unset($index);			
	foreach($content as $i => $c){
		if($c['startHour']==$hour){
			$index=$i;
			$split=$c['same'];
			$length=$c['endHour'] - $c['startHour']+1;
			$room=$c['roName'];
			$subject=$c['suShort'];
			$teacher=$c['teShort'];
			$ID=$c['ID'];
			$startHour = $c['startHour'];
			$comment = $c['comment'];
			$stuff=array('ID'=>$c['ID'],'split'=>$split,'length'=>$length,'roName'=>$room,'teShort'=>$teacher,'suShort'=>$subject, 'comment'=>$comment);

			unset($content[$i]);
		}
		if(isset($index)){
			break;
		}
	}
	if(!empty($length) && $length>1 && $startHour != $hour && $zeile1==1){
		$length--;
		$visibility="style=\"display:none\"";
	}
}

printf("<tr %s %s>\n",$rowID,$visibility);	//Zeilen 	Tag auf
 		printf("<form method=\"post\">\n");	//Formular Anfang Tag
 			//Für jeden Eintrag im Array field einmal diese Schleife durchlaufen
			 $hashGenerator->printForm();

foreach($field as $f) {	
	if(isset($index)){	//Wenn ein Content gefunden wurde
		//Content des aktuellen Inputs austauschen, Name des Array-Indizes ist der name des Inputs
		$f[5]=$stuff[$f[0]];
		$edit = "readonly";
	}
	else
		$edit = "";

	if($f[2] == "text") {
		printf("<td>%s <input type=\"text\" name=\"%s\" size=\"%spx\" value=\"%s\" %s >\n",//Textbox erstellen
				$f[1], $f[0], $f[3], $f[5], $f[6]);
	}
	else if($f[2] == "hidden") {
		printf("<td><input type=\"hidden\" name=\"%s\" value=\"%s\">\n",	//Versteckten Typ erstellen
				$f[0], $f[5]);

if($zeile1==1){
	printf("<td>Teilung: <input type=\"text\" name=\"%s\" id=\"%s\" size=\"2px\" value=\"%s\" onchange=\"javascript:changeText(%s);\" %s>\n",	//Textbox erstellen
		"visibilityText".$hour,"visibilityText".$hour,$stuff['split'],$hour,$edit);
	printf("<td><input type=\"button\" size=\"4px\" value=\"OK\" onclick=\"javascript:Visibility(%s)\" >\n", $hour);
	printf("<td>Stunde: <input type=\"text\" name =\"hour\" size=\"3px\" value=\"%s\" readonly >\n", $hour);
	printf("<td>L&auml;nge: <input type=\"text\" name=\"length\" id=\"%s\" size=\"5px\" value=\"%s\" onchange=\"javascript:visibleHours(%s);\" %s>\n",	//Textbox erstellen
		"visibleHour".$hour, $stuff['length'], $hour,$edit);
}
else{
	printf("<td><input type=\"hidden\" name=\"%s\" size=\"2px\" value=\"1\" >\n", "visibilityText".$hour);
	printf("<td><td>\n");	  						
	printf("<td><input type=\"hidden\" name =\"hour\" size=\"3px\" value=\"%s\" readonly >\n",	$hour);
	printf("<script type=\"text/javascript\">changeText(%s);</script>",$hour);
}
printf("<td><input type=\"hidden\" name =\"day\" value=\"%s\" >\n",	$_POST['day']);
printf("<td><input type=\"hidden\" name =\"class\" value=\"%s\" >\n",	//Textbox erstellen
	$_POST['class']);
	}
	else if($f[2] == "dropdown") {										//Dropdown Menü erstellen
		printf("<td>%s <input value=\"%s\" autocomplete=\"off\" size=\"%s\" list=\"%s\" name=\"%s\" %s></td>\n",
			$f[1], $f[5], $f[3], $f[0], $f[0], $f[6]);
	}
}
if($zeile1==1)
	form_savedelete(false);
else{
	printf("<td></td>\n");
	form_savedelete(true);
}
	printf("</form>\n");
 	printf("</tr>\n");
 	if(($zeile+1-(($hour-1)*7))==7)
 		printf("<script type=\"text/javascript\">Visibility(%s);</script>",$hour);
 	$zeile+=1;
}while($zeile<112);

printf("</table>\n");
}


function form_substitudes($fieldRow1,$fieldRow2,$content,$section,$hashGenerator)
{	

printf("<table style=\"width:100%%;text-align:right\">\n");	//Tabellen Tag auf
	printf("<form action=\"?section=%s\" method=\"post\">\n",$section);	//Formular Anfang Tag
 		$hashGenerator->printForm();
	$display["row"]= "none";
	$display["oldTeShort"] = "none";
	$display["oldStartHour"] = "none";
	$display["oldEndHour"] = "none";
	$display["move"] = "none";
	$display["add"] = "none";
	$display["remove"] = "none";
	$display["span"] = "none";
	$display["suShort"] = "table-cell";
	$display["teShort"] = "table-cell";
	$display["roName"] = "table-cell";
	$display["startHour"] = "table-cell";
	$display["endHour"] = "table-cell";
	$display["comment"] = "table-cell";
	$display["clName"] = "table-cell";
	$checked["free"]="";
	$checked["move"]="";
	$checked["add"]="";
	$checked["remove"]="";

if(!empty($content["hash"]))
	$color = "red";
else
	$color = "white";

if(!empty($content["free"]) && $content["free"]=="free"){
	if($content["freeRadio"]=="move")
		$content["move"]=true;
	else if($content["freeRadio"]=="add")
		$content["newSub"]=true;
	else if($content["freeRadio"]=="remove")
		$content["remove"]=true;
}

if($content["move"]){
	//echo "move";
	$checked["free"]="checked";
	$checked["move"]="checked";
	$display["move"] = "table-cell";
	$display["add"] = "table-cell";
	$display["remove"] = "table-cell";
	$display["row"]= "table-row";
	$display["oldTeShort"] = "table-cell";
	$display["oldStartHour"] = "table-cell";
	$display["oldEndHour"] = "table-cell";
}
else if ($content["remove"]){
	//echo "remove";
	$display["row"]= "table-row";
	$display["oldTeShort"] = "table-cell";
	$display["oldStartHour"] = "table-cell";
	$display["oldEndHour"] = "table-cell";
	$display["move"] = "table-cell";
	$display["add"] = "table-cell";
	$display["remove"] = "table-cell";
	$display["suShort"] = "none";
	$display["teShort"] = "none";
	$display["roName"] = "none";
	$display["startHour"] = "none";
	$display["endHour"] = "none";
	$display["span"] = "table-cell";
	$checked["free"]="checked";
	$checked["remove"]="checked";
}
else if($content["newSub"]){
	//echo "newSub";
	$display["row"]= "table-row";
	$display["move"] = "table-cell";
	$display["add"] = "table-cell";
	$display["remove"] = "table-cell";
	$checked["free"]="checked";
	$checked["add"]="checked";
}
printf("<tr>\n"); //Zeilen Tag auf
//Für jeden Eintrag im Array field einmal diese Schleife durchlaufen
foreach($fieldRow1 as $f) {	
	//Wenn Content mitgeliefert wird und nicht free oder add
	if($content!=false && $f[0]!="add" && $f[0]!="free"){
		 //Content des aktuellen Inputs austauschen, Name des Array-Indizes ist der name des Inputs
		$f[5]=$content[$f[0]];
	}
	
	if ($f[2] == "checkbox") { //Checkbox
		//Wenn gecheckt, dann Variable checked auf checked setzen
		if($f[5]==true){ 
			$checked="checked";
		}
		else{ //sonst leer
			$checked="";					
		}
		printf("<td id=\"visibleCell%s%s\" style=\"display:%s;width:140px\">%s <input type=\"checkbox\" name=\"%s\" %s value=\"%s\" %s>\n",	//Checkbox erstellen
			$ID,$f[0],$display[$f[0]],$f[1], $f[0], $checked, $f[0],$f[6]);
	}
	else if ($f[2] == "checkboxJava") {	//Checkbox
		if(empty($content["ID"]))
			$ID=0;
		else
			$ID=$content["ID"];
		//Checkbox erstellen
		printf("<td style=\"width:140px\">%s <input type=\"checkbox\" name=\"%s\" %s value=\"%s\" onclick=\"javascript:Visibility(%s,'%s')\">\n",	
			$f[1], $f[0], $checked[$f[0]], $f[0],$ID,$f[0]);
	}
	else if($f[2] == "text") {
		if($f[0]=="comment")
			printf("<td id=\"visibleSpan%s\" colspan=\"5\" style = \"display:%s\"></td>",$ID,$display["span"]);
		//Textbox erstellen
		printf("<td id=\"visibleCell%s%s\" style=\"display:%s\">%s <input id=\"%s%s\" type=\"text\" name=\"%s\" size=\"%spx\" value=\"%s\" %s >\n", 
			$ID,$f[0],$display[$f[0]],$f[1],$ID,$f[0], $f[0], $f[3], $f[5], $f[6]);
	}
	else if($f[2] == "hidden") {
		//Versteckten Typ erstellen
		printf("<td><input type=\"hidden\" name=\"%s\" value=\"%s\">\n", 
			$f[0], $f[5]);
	}
	else if($f[2] == "dropdown") { //Dropdown Menü erstellen
		printf("<td id=\"visibleCell%s%s\" style=\"display:%s\">%s <input value=\"%s\" autocomplete=\"off\" size=\"%s\" list=\"%s\" name=\"%s\" %s></td>\n",
			$ID,$f[0],$display[$f[0]],$f[1], $f[5], $f[3], $f[0], $f[0], $f[6]);		
	}
}
	
if($content==false){
	printf("<td colspan=\"5\"></td>");
	form_savedelete(true);
}
else{	
	form_savedelete(false);
}
				
printf("</tr>\n");
printf("<tr id=\"visibleRow%s\" style=\"display:%s\">\n",$ID,$display["row"]); //Zeilen Tag auf
		//Für jeden Eintrag im Array field einmal diese Schleife durchlaufen
		printf("<td colspan=\"2\"></td>\n");
foreach($fieldRow2 as $f) {
	//Wenn Content mitgeliefert wird, dann Content austauschen		
	if($content!=false && $f[0]!="add" && $f[0]!="free"){ 
		//Content des aktuellen Inputs austauschen, Name des Array-Indizes ist der name des Inputs
		$f[5]=$content[$f[0]];
	}
	
	if($f[2] == "text") {
		//Textbox erstellen
		printf("<td id=\"visibleCell%s%s\" style=\"display:%s\">%s <input  type=\"text\" name=\"%s\" size=\"%spx\" value=\"%s\" %s >\n", 
			$ID,$f[0],$display[$f[0]],$f[1], $f[0], $f[3], $f[5], $f[6]);
	}		
	else if($f[2] == "dropdown") { //Dropdown Menü erstellen
		printf("<td id=\"visibleCell%s%s\" style=\"display:%s\">%s <input value=\"%s\" autocomplete=\"off\" size=\"%s\" list=\"teShort\" name=\"%s\" %s></td>\n", $ID,$f[0],$display[$f[0]],$f[1],$f[5], $f[3], $f[0], $f[6]);
	}
	else if ($f[2] == "radio") { //Checkbox		
		printf("<td id=\"visibleCell%s%s\" style=\"display:%s\">%s <input type=\"radio\" id=\"visibleRadio%s%s\" name=\"freeRadio\" %s value=\"%s\"  %s onclick=\"javascript:Visibility(%s,'%s')\">\n", $ID,$f[0],$display[$f[0]],$f[1], $ID,$f[0],$checked[$f[0]], $f[0],$f[6],$ID,$f[0]);
	}				
}
	
  	printf("</tr>\n");
	printf("</form>\n");
printf("</table>\n");
printf("<hr style=\"color:%s\"\>",$color);
}


/*
 *Erstellt die Schaltflächen für Speichern und Löschen
 *$new ist eine Boolsch Variable
 *True bedeutet dass es ein leeres Formular ist --> Kein Löschen
 *False bedeutet kein neues Formular --> Löschen
 */
function form_savedelete($new)
{

if($new!=true)
	printf("<td style=\"text-align:right; width:111px\"> L&ouml;schen<input type=\"checkbox\" name=\"delete\" value=\"delete\"></td>\n");		//Checkbox erstellen

printf("<td style=\"text-align:right;width:86px\"><input type=\"submit\" name=\"save\" value=\"&Uuml;bernehmen\"></td>\n");	//Submit Button erstellen

}
?>

