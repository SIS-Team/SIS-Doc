SIS-Doc
=======

Die Dokumentation für SIS

Quergelesen: gruber 8.5, branch read-may8 for typos
(delete at will)

* most beautiful "Das Monitorsystem läuft
  ohne grobe Ausfälle, gegen die wir Maßnahmen treffen könnten."

* 2.4 -- wth is github ? "Dazu wurde GitHub verwendet."
  maybe "Dazu wurde ein Versionsverwaltungs (VCS Versioncontrolsystem), 
  hier Github, verwendet, da moderne VCS kollaboratives und 
  gleichzeitiges arbeiten unterstützen."

* 3.1.1.1 -- Host Als Host wird die Adresse des gewünschten Servers eingetragen. 
  Sie wird in eine IP-Adresse umgewandelt und dem TCP übergeben. 

  This hostname will not be resolved to an ip, because it is unnecessary, this is 
  the data sent to the host, and this field is vital for virtual hosts, having
  more than one domainname on one ipaddress served by one webserver.
  
  This is IMHO "the" difference between HTTP1.0 and HTTP1.1
  
* 3.1.3.1 another beauty "Auch wenn PHP inzwischen vollständig objektorientiert ist, kann immer noch funktional
programmiert werden."
  funktional acchording to duden "ein funktionaler (zweckmäßiger, von seiner Funktion her bestimmter) Stil"
  means workable. Functional programming is a specific class of programming languages, the term procedural
  would be the correct one, except it is meant ironic.

* p16 "die häufig verwendete Programmteile zwischenspeichern" this cache is the drive buffer of the operating
  system, caching the php-file, the server cache stores processed files the results, that will be sent to
  the client.
  
* p16 -- "PHP hat auch eine Möglichkeit, diverse Daten dem Server zu senden, diese können von der zu empfangenden Webseite" 

  POST and GET are HTTP elements not PHP functionality, PHP is capable of accessing POST/GET data.
  
* p18 -- "Die Datenlänge bei GET besteht darin, ..." not really meaningful :-)

* p19 -- "Probleme" -- empty section, means no problem, great.

* p23 -- Question what happens if the key is not unique in the foreign table ?

* p24 -- CAUTION do not use TRUNCATE unless you are really sure. If IDs numbering starts over again
  foreign keys will be referencing the wrong entries.

* p36 -- "eckige Klammer" better "spitze" ?

* p37 -- never came about "</br>", HTML had "<br>" without any closer, because there is no content.

* p37 -- missing "> in the example or is it "/>" for xhtml.

* p40 -- Abbildung 3.8 missing

* p48 -- sql incetion string quote character hell, killed by latex smartness , put into verbatim.

* p58 -- Abbildung 3.11 missing

* p59 -- "Die kombinierten Felder sollten den ersten Wert enthalten." first of what
