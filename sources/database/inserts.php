<?php

	/* /modules/database/inserts.php
	 * Autor: Handle Marco
	 * Beschreibung:
	 *	Insert Befehle für die Datenbank von den Formularen
	 *	
	 *
	 */
//Stell Datumfunktionen zur Verfügung
include(ROOT_LOCATION . "/modules/other/dateFunctions.php");					

//Insert von dem Klassen Formular
function classes(){

//alle Post Parameter in eine Variable schreiben
$post=$_POST;

//Save Parameter im Array löschen
unset($post["save"]);

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","name" => "","sectionFK" => "","teacherFK" => "","roomFK" => "","invisible" => "");
//Mitgegebene ID ins Daten-Array schreiben
$data["ID"]=$post["ID"];

//Klassenname ins Daten-Array schreiben
$data["name"]=htmlentities($post["clName"]);
//FK von der Section aus der Datenbank abfragen
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM sections WHERE short='".mysql_real_escape_string(htmlspecialchars($post["seShort"]))."'"));
//Kontrollieren ob eine ID zurückgegeben wurde
$ok1 = control($post["seShort"],$temp["ID"],"Abteilung");
//In Daten-Array schreiben	
$data["sectionFK"] = $temp["ID"];	
//FK vom Lehrer aus der Datenbank abfragen
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["teShort"]))."'"));
//Kontrollieren ob eine ID zurückgegeben wurde
$ok2 = control($post["teShort"],$temp["ID"],"Lehrer");	
//In Daten-Array schreiben
$data["teacherFK"] = $temp["ID"];	
//FK vom Raum aus der Datenbank abfragen
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM rooms WHERE name='".mysql_real_escape_string(htmlspecialchars($post["roName"]))."'"));
//Kontrollieren ob eine ID zurückgegeben wurde
$ok3 = control($post["roName"],$temp["ID"],"Raum");	
//In Daten-Array schreiben
$data["roomFK"] = $temp["ID"];	

//Wenn Invisible ausgewählt dann True in die DB schreiben
if(!empty($post["invisible"]))
	$data["invisible"]=true;

//Wenn Delete nicht ausgewählt ist und alle Kontrollen erfolgreich dann in DB schreiben
if(empty($post["delete"]) && ($ok1*$ok2*$ok3) == 1)
	saveupdate($data,"classes");
}

//Insert von dem Stunden Formular 
function hours(){

$post=$_POST;	//alle Post Parameter in eine Variable schreiben

unset($post["save"]);	//Save Parameter im Array löschen

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","weekday" => "","weekdayShort" => "","hour" => "","startTime" => "","endTime" => "");

//Alles Werte in das Daten-Array schreiben
$data["ID"]=$post["ID"];
$data["weekday"]=$post["weekday"];
$data["weekdayShort"]=$post["weekdayShort"];
$data["hour"]=$post["hour"];
$data["startTime"]=$post["startTime"];
$data["endTime"]=$post["endTime"];

//Wenn Delete nicht ausgewählt ist dann in DB schreiben
if(empty($post["delete"]))
	saveupdate($data,"hours");
}

//Insert von dem Stundenplan Formular 
function lessons(){

//alle Post Parameter in eine Variable schreiben
$post=$_POST;
//Save Parameter im Array löschen
unset($post["save"]);

//Definition der Spalten in der MYSQL Tabelle
$lessonsInsert=array("ID" => "","lessonBaseFK" => "","roomFK" => "","teachersFK" => "","subjectFK" => "","comment"=>"");
//Definition der Spalten in der MYSQL Tabelle
$lessonsBaseInsert=array("ID" => "","startHourFK" => "","endHourFK" => "","classFK" => "");
//Mitgegebene ID ins Daten-Array schreiben
$lessonsInsert["ID"]=$post["ID"];	

//Alles Werte in das Daten-Array schreiben
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE hour='".intval($post["hour"])."' AND weekdayShort='".mysql_real_escape_string(htmlspecialchars($post["day"]))."'"));
$lessonsBaseInsert["startHourFK"] = $temp["ID"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE hour='".intval($post["hour"]+$post["length"]-1)."' AND weekdayShort='".mysql_real_escape_string(htmlspecialchars($post["day"]))."'"));
$lessonsBaseInsert["endHourFK"] = $temp["ID"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM classes WHERE name='".mysql_real_escape_string(htmlspecialchars($post["class"]))."'"));
$lessonsBaseInsert["classFK"] = $temp["ID"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM lessonsBase WHERE startHourFK='".$lessonsBaseInsert["startHourFK"]."' AND classFK='".$lessonsBaseInsert["classFK"]."'"));
$lessonsBaseInsert["ID"] = $temp["ID"];

//Alles Werte in das Daten-Array schreiben
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["teShort"]))."'"));
$ok1 = control($post["teShort"],$temp["ID"],"Lehrer");
$lessonsInsert["teachersFK"] = $temp["ID"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM rooms WHERE name='".mysql_real_escape_string(htmlspecialchars($post["roName"]))."'"));
$ok2 = control($post["roName"],$temp["ID"],"Raum");
$lessonsInsert["roomFK"] = $temp["ID"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM subjects WHERE short = '".mysql_real_escape_string(htmlspecialchars($post["suShort"]))."'"));
$ok3 = control($post["suShort"],$temp["ID"],"Fach");
$lessonsInsert["subjectFK"]=$temp["ID"];
$lessonsInsert["comment"]=htmlspecialchars($post["comment"]);

//Wenn keine Basisstunde gefunden wurde und alles andere vorhanden ist dann soll eine neue Basistunde erstellt werden
if($lessonsBaseInsert['ID']=="" && ($ok1*$ok2*$ok3) == 1){
	saveupdate($lessonsBaseInsert,"lessonsBase");
	$temp = mysql_fetch_array(mysql_query("SELECT ID FROM lessonsBase WHERE startHourFK='".$lessonsBaseInsert["startHourFK"]."' AND endHourFK='".$lessonsBaseInsert["endHourFK"]."' AND classFK='".$lessonsBaseInsert["classFK"]."'"));
	$lessonsBaseInsert["ID"] = $temp["ID"];	
}

$lessonsInsert["lessonBaseFK"]=$lessonsBaseInsert["ID"];	//FK der Basisstunde in Array speichern

//Wenn die Stunde nicht gelöscht werden soll dann erstellen
if(empty($post["delete"]) && ($ok1*$ok2*$ok3) == 1)
	saveupdate($lessonsInsert,"lessons");
else if(($ok1*$ok2*$ok3) == 1)	//Wenn löschen gesetzt ist, dann alle Stunden und Basisstunden löschen
{
	$lessonsbaseID=$lessonsInsert["lessonBaseFK"];
	//Lessons löschen
	$sql="DELETE FROM lessons WHERE lessonBaseFK = '".$lessonsbaseID."'";
	mysql_query($sql);
	//LessonsBase löschen
	$sql="DELETE FROM lessonsBase WHERE ID = '".$lessonsbaseID."'";
	mysql_query($sql);
}
}

//Insert von den Fehlenden Klassen Formular 
function missingClasses(){

$post=$_POST;	//alle Post Parameter in eine Variable schreiben

unset($post["save"]);	//Save Parameter im Array löschen

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","classFK" => "","startDay" => "","startHourFK" => "","endDay" => "","endHourFK" => "","reason" => "");

//Alles Werte in das Daten-Array schreiben
$data["ID"]=$post["ID"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM classes WHERE name='".mysql_real_escape_string(htmlspecialchars($post["clName"]))."'"));
$ok1 = control($post["clName"],$temp["ID"],"Klasse");
$data["classFK"]=$temp["ID"];
$day = weekday($post["startDay"]);
$data["startDay"]=$post["startDay"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["startHour"])."'"));
$ok2 = control($post["startHour"],$temp["ID"],"Start-Stunde");
$data["startHourFK"]=$temp["ID"];
$day = weekday($post["endDay"]);
$data["endDay"]=$post["endDay"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["endHour"])."'"));
$ok3 = control($post["endHour"],$temp["ID"],"End-Stunde");
$data["endHourFK"]=$temp["ID"];
$data["reason"]=htmlspecialchars($post["reason"]);

//Wenn die Kontrolle erfolgreich
if( ($ok1*$ok2*$ok3)==1){
	if(empty($post["delete"]))	//Wenn nicht löschen
		saveupdate($data,"missingClasses");	//speichern
	else	//sonst
		deleteID($data["ID"],"missingClasses");		//löschen
}	
}

//Insert von den Fehlenden Lehrer Formular 
function missingTeachers(){

$post=$_POST;	//alle Post Parameter in eine Variable schreiben

unset($post["save"]);	//Save Parameter im Array löschen

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","teacherFK" => "","startDay" => "","startHourFK" => "","endDay" => "","endHourFK" => "","reason" => "");

//Alles Werte in das Daten-Array schreiben
$data["ID"]=$post["ID"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["teShort"]))."'"));
$ok1 = control($post["teShort"],$temp["ID"],"Lehrer");
$data["teacherFK"]=$temp["ID"];
$day = weekday($post["startDay"]);
$data["startDay"]=$post["startDay"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["startHour"])."'"));
$ok2 = control($post["startHour"],$temp["ID"],"Start-Stunde");
$data["startHourFK"]=$temp["ID"];
$day = weekday($post["endDay"]);
$data["endDay"]=$post["endDay"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["endHour"])."'"));
$ok3 = control($post["endHour"],$temp["ID"],"End-Stunde");
$data["endHourFK"]=$temp["ID"];
$data["reason"]=htmlspecialchars($post["reason"]);

//Wenn die Kontrolle erfolgreich ist
if( ($ok1*$ok2*$ok3)==1){
	if(empty($post["delete"]))	//Wenn nicht löschen
		saveupdate($data,"missingTeachers");	//speichern
	else	//sonst
		deleteID($data["ID"],"missingTeachers");	//löschen
}
}

//Insert von dem Raum Formular
function rooms(){

$post=$_POST;	//alle Post Parameter in eine Variable schreiben

unset($post["save"]);	//Save Parameter im Array löschen

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","name" => "","teacherFK" => "");

//Alles Werte in das Daten-Array schreiben
$data["ID"]=$post["ID"];
$data["name"]=htmlspecialchars($post["roName"]);
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["teShort"]))."'"));
$ok1 = control($post["teShort"],$temp["ID"],"Lehrer");
$data["teacherFK"]=$temp["ID"];

//Wenn die Kontrolle erfolgreich ist und löschen nicht gesetzt ist
if(empty($post["delete"]) && $ok1==1)
	saveupdate($data,"rooms"); 	//dann speichern
	
}

//Insert von dem Raum Formular
function sections(){

$post=$_POST;	//alle Post Parameter in eine Variable schreiben

unset($post["save"]);	//Save Parameter im Array löschen

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","name" => "","short" => "","teacherFK" => "");

//Alles Werte in das Daten-Array schreiben
$data["ID"]=$post["ID"];
$data["name"]=htmlspecialchars($post["seName"]);
$data["short"]=$post["seShort"];
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["teShort"]))."'"));
$ok1 = control($post["teShort"],$temp["ID"],"Lehrer");
$data["teacherFK"]=$temp["ID"];

//Wenn die Kontrolle erfolgreich ist und löschen nicht gesetzt ist
if(empty($post["delete"]) && $ok1 == 1)
	saveupdate($data,"sections");	//dann speichern
}

//Insert von dem Fächer Formular
function subjects(){

$post=$_POST;	//alle Post Parameter in eine Variable schreiben

unset($post["save"]);	//Save Parameter im Array löschen

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","name" => "","short" => "","invisible" => "");

//Alles Werte in das Daten-Array schreiben
$data["ID"]=$post["ID"];
$data["name"]=htmlspecialchars($post["name"]);
$data["short"]=htmlspecialchars($post["short"]);
//Wenn Invisible gesetzt dann true in die DB schreiben
if(!empty($post["invisible"]))
	$data["invisible"]=true;

//Wenn löschen nicht gesetzt ist
if(empty($post["delete"]))
	saveupdate($data,"subjects");	
}

//Insert von dem Supplierplan Formular
function substitudes(){

//alle Post Parameter in eine Variable schreiben
$post=$_POST;

//Save Parameter im Array löschen
unset($post["save"]);

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","time" => "","newSub" => "","remove" => "","move" => "","lessonFK" => "","startHourFK" => "","endHourFK" => "","teacherFK" => "","subjectFK" => "","roomFK" => "","classFK" => "","comment" => "");

//Wenn die ID nicht leer ist, dann update, also zuvor Supplierung aus DB löschen
if($post["ID"]!=""){	
	
	//ID löschen
	$sql="DELETE FROM substitudes WHERE ID=".$post["ID"];
	mysql_query($sql);
	$post["ID"]="";

}
//Neue Supplierung erstellen, wenn delete nicht gesetzt
if(empty($post["delete"])){
	//Wenn die Eingabe eine Freie eingabe ist
	if(!empty($post["free"]) && $post["free"]=="free"){
		//Wenn die Stunde hinzugefügt wird
		if($post["freeRadio"]=="add"){
			//Alles Werte in das Daten-Array schreiben
			$data["lessonFK"]=0;
			$day = weekday($post["time"]);
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM subjects WHERE short='".mysql_real_escape_string(htmlspecialchars($post["suShort"]))."'"));
			$ok1 = control($post["suShort"],$temp["ID"],"Fach");		
			$data["subjectFK"]= $temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["teShort"]))."'"));
			$ok2 = control($post["teShort"],$temp["ID"],"Lehrer");		
			$data["teacherFK"]=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM rooms WHERE name='".mysql_real_escape_string(htmlspecialchars($post["roName"]))."'"));
			$ok3 = control($post["roName"],$temp["ID"],"Raum");		
			$data["roomFK"]=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["startHour"])."'"));
			$ok4 = control($post["startHour"],$temp["ID"],"Start-Stunde");		
			$data["startHourFK"]=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["endHour"])."'"));
			$ok5 = control($post["endHour"],$temp["ID"],"End-Stunde");		
			$data["endHourFK"]=$temp["ID"];
			$data["time"]=$post["time"];
			$data["comment"]=htmlspecialchars($post["comment"]);
			$data["newSub"]=true;
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM classes WHERE name='".mysql_real_escape_string(htmlspecialchars($post['clName']))."'"));
			$ok6 = control($post["clName"],$temp["ID"],"Klasse");		
			$data["classFK"] = $temp['ID'];
			//Wenn die Kontrolle erfolgreich ist
			if(($ok1*$ok2*$ok3*$ok4*$ok5*$ok6) ==1 )
				saveupdate($data,"substitudes");
		}
		else if($post["freeRadio"]=="remove"){	//Wenn die Stunde gelöscht wird
			//Alles Werte in das Daten-Array schreiben
			$data["comment"]=htmlspecialchars($post["comment"]);
			$data["remove"]=true;
			$day = weekday($post["time"]);
			$data["time"]=$post["time"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["oldTeShort"]))."'"));
			$ok1 = control($post["teShort"],$temp["ID"],"Lehrer");		
			$teacher=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["oldStartHour"])."'"));
			$ok2 = control($post["oldStartHour"],$temp["ID"],"Urs. Start-Stunde");		
			$startHour=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["oldEndHour"])."'"));
			$ok3 = control($post["oldEndHour"],$temp["ID"],"Urs. End-Stunde");		
			$endHour=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM classes WHERE name='".mysql_real_escape_string(htmlspecialchars($post['clName']))."'"));
			$ok4 = control($post["clName"],$temp["ID"],"Klasse");		
			$class = $temp['ID'];
			$data["classFK"] = $temp['ID'];
			//Wenn die Kontrolle erfolgreich ist
			if(($ok1*$ok2*$ok3*$ok4)==1){
				//Wenn kein Lehrer eingegeben wurde --> Für alle Lehrer zu dieser Stunde in dieser Klasse usw. wird eine Supplierung eingetragen
if($teacher==""){
	//ID's der ganzen Lessons für die Basisstunde finden
	$sql = "SELECT lessons.ID FROM lessons 
		INNER JOIN lessonsBase ON lessonsBase.ID=lessons.lessonBaseFK 
		WHERE lessonsBase.startHourFK='".$startHour."' AND lessonsBase.endHourFK='".$endHour."' AND lessonsBase.classFK='".$class."'";
	$result = mysql_query($sql);			
	while($row[]=mysql_fetch_array($result)){
	}
	unset($row[count($row)-1]);
	if($row != array()){
		foreach($row as $i => $r){
			$data["lessonFK"]=$r["ID"];
			saveupdate($data,"substitudes");
		}
	}
	else
		return false;
}
//Wenn ein Lehrer mitgegeben wurde nur für diesen eine Supplierung eintragen
else{
	//ID der Lesson für die Basisstunde und dem Lehrer finden
	$sql = "SELECT lessons.ID FROM lessons 
		INNER JOIN lessonsBase ON lessonsBase.ID=lessons.lessonBaseFK 
		WHERE lessonsBase.startHourFK='".$startHour."' AND lessonsBase.endHourFK='".$endHour."' AND lessonsBase.classFK='".$class."' AND lessons.teachersFK='".$teacher."'";
	$temp = mysql_fetch_array(mysql_query($sql));
	$data["lessonFK"]=$temp["ID"];
	if($temp["ID"]!="")
		saveupdate($data,"substitudes");
	else 
		return false;
}
			}	
		}
		//Wenn die Stunde verschoben wird
		else{	
			//Alles Werte in das Daten-Array schreiben
			$day = weekday($post["time"]);
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM subjects WHERE short='".mysql_real_escape_string(htmlspecialchars($post["suShort"]))."'"));
			$ok1 = control($post["suShort"],$temp["ID"],"Fach");		
			$data["subjectFK"]= $temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["teShort"]))."'"));
			$ok2 = control($post["teShort"],$temp["ID"],"Lehrer");
			$data["teacherFK"]=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM rooms WHERE name='".mysql_real_escape_string(htmlspecialchars($post["roName"]))."'"));
			$ok3 = control($post["roName"],$temp["ID"],"Raum");		
			$data["roomFK"]=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["startHour"])."'"));
			$ok4 = control($post["startHour"],$temp["ID"],"Start-Stunde");		
			$data["startHourFK"]=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["endHour"])."'"));
			$ok5 = control($post["endHour"],$temp["ID"],"End-Stunde");		
			$data["endHourFK"]=$temp["ID"];
			$data["comment"]=htmlspecialchars($post["comment"]);
			$data["move"]=true;
			$data["time"]=$post["time"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["oldTeShort"]))."'"));
			$ok6 = control($post["oldTeShort"],$temp["ID"],"Urs. Lehrer");		
			$teacher=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["oldStartHour"])."'"));
			$ok7 = control($post["oldStartHour"],$temp["ID"],"Urs. Start-Stunde");		
			$startHour=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["oldEndHour"])."'"));
			$ok8 = control($post["oldEndHour"],$temp["ID"],"Urs. End-Stunde");		
			$endHour=$temp["ID"];
			$temp = mysql_fetch_array(mysql_query("SELECT ID FROM classes WHERE name='".mysql_real_escape_string(htmlspecialchars($post['clName']))."'"));
			$ok9 = control($post["clName"],$temp["ID"],"Klasse");		
			$class = $temp['ID'];
			$data["classFK"] = $temp['ID'];
			//Wenn die Kontrolle erfolgreich ist
			if(($ok1*$ok2*$ok3*$ok4*$ok5*$ok6*$ok7*$ok8*$ok9)==1){
				//Wenn kein Lehrer eingegeben wurde --> Für alle Lehrer zu dieser Stunde in dieser Klasse usw. wird eine Supplierung eingetragen
				if($teacher==""){
					//ID's der ganzen Lessons für die Basisstunde finden
					$sql = "SELECT lessons.ID FROM lessons INNER JOIN lessonsBase ON lessonsBase.ID=lessons.lessonBaseFK WHERE lessonsBase.startHourFK='".$startHour."' AND lessonsBase.endHourFK='".$endHour."' AND lessonsBase.classFK='".$class."'";
					$result = mysql_query($sql);			
					while($row[]=mysql_fetch_array($result)){
					}
					unset($row[count($row)-1]);
					if($row != array()){
						foreach($row as $i => $r){
							$data["lessonFK"]=$r["ID"];
							saveupdate($data,"substitudes");
						}
					}
					else
						return false;
				}
				//Wenn ein Lehrer mitgegeben wurde nur für diesen eine Supplierung eintragen
				else{
					//ID der Lesson für die Basisstunde und dem Lehrer finden
					$sql = "SELECT lessons.ID FROM lessons INNER JOIN lessonsBase ON lessonsBase.ID=lessons.lessonBaseFK WHERE lessonsBase.startHourFK='".$startHour."' AND lessonsBase.endHourFK='".$endHour."' AND lessonsBase.classFK='".$class."' AND lessons.teachersFK='".$teacher."'";
					$temp = mysql_fetch_array(mysql_query($sql));
					$data["lessonFK"]=$temp["ID"];
					if($temp["ID"]!="")
						saveupdate($data,"substitudes");
					else 
						return false;
				}	
			}
		}
	}
	//Wenn die Supplierung nicht frei eingetragen wird
	else{
		//Alles Werte in das Daten-Array schreiben
		$day = weekday($post["time"]);
		$temp = mysql_fetch_array(mysql_query("SELECT ID FROM subjects WHERE short='".mysql_real_escape_string(htmlspecialchars($post["suShort"]))."'"));
		$ok1 = control($post["suShort"],$temp["ID"],"Fach");	
		$data["subjectFK"]= $temp["ID"];
		$temp = mysql_fetch_array(mysql_query("SELECT ID FROM teachers WHERE short='".mysql_real_escape_string(htmlspecialchars($post["teShort"]))."'"));
		$ok2 = control($post["teShort"],$temp["ID"],"Lehrer");
		$data["teacherFK"]=$temp["ID"];
		$temp = mysql_fetch_array(mysql_query("SELECT ID FROM rooms WHERE name='".mysql_real_escape_string(htmlspecialchars($post["roName"]))."'"));
		$ok3 = control($post["roName"],$temp["ID"],"Raum");	
		$data["roomFK"]=$temp["ID"];
		$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["startHour"])."'"));
		$ok4 = control($post["startHour"],$temp["ID"],"Start-Stunde");		
		$data["startHourFK"]=$temp["ID"];
		$temp = mysql_fetch_array(mysql_query("SELECT ID FROM hours WHERE weekdayShort='".$day."' AND hour='".intval($post["endHour"])."'"));
		$ok5 = control($post["endHour"],$temp["ID"],"End-Stunde");	
		$data["endHourFK"]=$temp["ID"];
		$data["comment"]=htmlspecialchars($post["comment"]);
		$data["time"]=$post["time"];
		$temp = mysql_fetch_array(mysql_query("SELECT ID FROM classes WHERE name='".mysql_real_escape_string(htmlspecialchars($post['clName']))."'"));
		$ok6 = control($post["clName"],$temp["ID"],"Klasse");		
		$class = $temp['ID'];
		$data["classFK"] = $temp['ID'];
		//Wenn die Kontrolle erfolgreich ist
		if(($ok1*$ok2*$ok3*$ok4*$ok5*$ok6)==1){
			//Alles fehlende Lehrer die zur Supplierzeit fehlen und die die Supplierung betrifft finden
$sql = "SELECT missingTeachers.teacherFK,missingTeachers.startDay, missingTeachers.endDay,hourST.hour as startHour, hourEN.hour as endHour FROM missingTeachers 
	INNER JOIN hours as hourST ON hourST.ID = missingTeachers.startHourFK 
	INNER JOIN hours as hourEN ON hourEN.ID = missingTeachers.endHourFK 
	INNER JOIN lessons ON missingTeachers.teacherFK = lessons.teachersFK 
	INNER JOIN lessonsBase ON lessonsBase.ID = lessons.lessonBaseFK 
	WHERE missingTeachers.startDay <='".mysql_real_escape_string(htmlspecialchars($post['time']))."' AND missingTeachers.endDay >='".mysql_real_escape_string(htmlspecialchars($post['time']))."' AND lessonsBase.startHourFK='".$data["startHourFK"]."' AND lessonsBase.endHourFK='".$data["endHourFK"]."' AND lessonsBase.classFK='".$class."'";
$temp = mysql_query($sql);
while($missTeacher[] = mysql_fetch_array($temp)){
}
unset($missTeacher[count($missTeacher)-1]);

if(!empty($missTeacher)){
	//Für jeden fehlenden Lehrer eine Supplierung eintragen
	foreach($missTeacher as $i => $m){
		//Lessons ID finden
		if(($m['startDay']==$data['time'] && $m['startHour']<=$post['startHour']) || ($m['endDay']==$data['time'] && $m['endHour']>=$post['endHour']) || ($m['startDay']<$data['time'] && $data['time'] < $m['endDay'])){
			$sql = "SELECT lessons.ID FROM lessons 
				INNER JOIN lessonsBase ON lessonsBase.ID = lessons.lessonBaseFK 
				WHERE lessonsBase.startHourFK ='".$data["startHourFK"]."' AND lessonsBase.endHourFK='".$data["endHourFK"]."' AND lessonsBase.classFK='".$class."' AND lessons.teachersFK = '".$m["teacherFK"]."'";
			$temp = mysql_query($sql);
			$temp = mysql_fetch_row($temp);
			$data["lessonFK"] = $temp[0];
			if($temp[0]!="")
				saveupdate($data,"substitudes");

		}
		else
			return false;
	}
//Wenn kein fehlender Lehrer gefunden ist Fehler zurückgeben --> falsche Eingabe
}
else
	return false;	//sonst true
		}	
	}
}
return true;	//true
}

//Insert von dem Lehrer Formular
function teachers(){

$post=$_POST;	//alle Post Parameter in eine Variable schreiben

unset($post["save"]);	//Save Parameter im Array löschen

//Definition der Spalten in der MYSQL Tabelle
$data=array("ID" => "","name" => "","short" => "","display" => "","sectionFK" => "","invisible" => "");

//Alles Werte in das Daten-Array schreiben
$data["ID"]=$post["ID"];
$data["name"]=htmlspecialchars($post["teName"]);
$data["short"]=$post["teShort"];
$data["display"]=htmlspecialchars($post["display"]);
$temp = mysql_fetch_array(mysql_query("SELECT ID FROM sections WHERE short='".mysql_real_escape_string(htmlspecialchars($post["seShort"]))."'"));
$ok1 = control($post["seShort"],$temp["ID"],"Abteilung");	
$data["sectionFK"] = $temp["ID"];
//Wenn Invisible gesetzt dann true in die DB schreiben
if(!empty($post["invisible"]))
	$data["invisible"]=true;
//Wenn die Kontrolle erfolgreich ist und löschen nicht gesetzt ist
if(empty($post["delete"]) && $ok1 == 1)
	saveupdate($data,"teachers");	
}

//Funktion um in die Datenban zu speichern oder Datensätze zu aktualisieren
//$insert....Daten mit dem dazugehörigen Spaltenname als Index innerhalb des Arrays
//$tabel.....Tabellenname als String
function saveUpdate($insert,$table){
	//Wenn ID leer ist --> Neuer Datensatz
	if(empty($insert['ID']))
	{
$col="";	//String für die Spaltennamen
$dat="";	//String für die Daten
$len=count($insert);	//Länge des Arrays zählen
$lauf=1;	//Laufvariable auf 1 setzen

//Jede Spalte durchlaufen
foreach($insert as $i => $p)
{
	if($i != "ID"){	//Index ID nicht verwenden
		$col.= $i; //Index (Spaltenname)
		$dat.= "'".$p."'";	//Daten
		//Wenn es nicht die letzte Spalte ist Beistrich anfügen
		if($lauf < $len){	
			$col.=" , "
			$dat.=" , ";
		}
	}
	$lauf+=1; //Laufvariable erhöhen		
}
//Insert Befehl aufbauen
$sql="INSERT INTO ".$table." (".$col.") VALUES (".$dat.")";	
	
	}//Wenn die ID gegeben ist --> Update
else if(!empty($insert['ID']))
{
	$dat=""; //String für die Daten
	$len=count($insert); //Länge der Arrays zählen
	$lauf=1; //Laufvariable auf 1 setzen

	//Jede Spalte durchlaufen
	foreach($insert as $i => $p)
	{
		if($i != "ID"){	//Index ID nicht verwenden
			$dat.= $i." = '".$p."'"; //Daten
			//Wenn es nicht die letzte Spalte ist Beistrich anfügen
			if($lauf < $len)	
				$dat.=" , ";
		}
		$lauf+=1; //Laufvariable erhöhen	
	}
	$sql="UPDATE ".$table." SET ".$dat." WHERE ID = '".$insert["ID"]."'"; //Update Befehl aufbauen
}
mysql_query($sql); //Befehl ausführen
}

//Funktion zum löschen eines Datensatzes
//$ID......Die Datensatz ID
//$table...Tabellenname als String
function deleteID($ID,$table){
//Delete Befehl aufbauen
$sql="DELETE FROM ".$table." WHERE ID = '".$ID."'";
mysql_query($sql);	//Befehl ausführen
}

//Funktion zum Kontrollieren von Selects und Post
//Wird ein Post Wert gegeben und der Select ist leer --> Fehler
//Beides leer --> richtig
//Beides befüllt --> richtig
//$post......Mitgabewerte des Formulars
//$select....ID des Selects
//$field.....Name des nicht gefundenen Wertes als String
function control($post,$select,$field)
{	//Wenn Post nicht leer, aber select leer --> Fehler
	if($post != "" && $select == ""){	
		//Alert ausgeben
		printf("<script> window.alert('%s nicht gefunden!');</script> ",$field);	
		return 0;	//0 zurückgeben
	}
	return 1;
}

?>
