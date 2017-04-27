<?php

$MY_CNF = parse_ini_file($_SERVER['HOME']."/.my.cnf", true);
$DEBUG = 1;
$SPECIES = array('Canine Tests' => 'Canine');



$mysqli = mysqli_connect("localhost", $MY_CNF['client']['user'], $MY_CNF['client']['password'], "es_webshop_import");
if (mysqli_connect_errno($mysqli)) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error()."\n";
	exit(0);
}

//CLIENTS (people)
$SQL = "SELECT GROUP_CONCAT(distinct ClientID) as client_id, FullName, Organisation, Email, Tel, Fax, Address, Address2, Address3, Town, County, Postcode,
		Country, ShippingName, ShippingCompany, ShippingAddress, ShippingAddress2, ShippingAddress3, ShippingTown, ShippingCounty, ShippingPostcode, ShippingCountry
		FROM webshop_import WHERE imported=0 GROUP BY FullName, Email";
if ($DEBUG) { echo str_replace("\t", "", $SQL)."\n"; }

$RESULT = mysqli_query($mysqli, $SQL);
while ($row = mysqli_fetch_assoc($RESULT)){
	
	$SQL2 = 'SELECT * FROM client WHERE 
			(FullName = "'.$row['FullName'].'") + 
			(Email = "'.$row['Email'].'") + 
			(ClientID = '.$row['client_id'].') > 2';
	if ($DEBUG) { echo str_replace("\t", "", $SQL2)."\n"; }
	$RESULT2 = mysqli_query($mysqli, $SQL2);
	if (mysqli_num_rows($RESULT2) > 0){
		echo "Client already found in the database - record needs updating\n";
	}
	else{
		echo "Client needs a record creating in the database\n";
		$SQL3 = 'INSERT INTO client(ClientID, FullName, Organisation, Email, Tel, Fax, Address, Address2, Address3, Town, County, Postcode,
				Country, ShippingName, ShippingCompany, ShippingAddress, ShippingAddress2, ShippingAddress3, ShippingTown, ShippingCounty, ShippingPostcode, ShippingCountry)
				VALUES ("'.$row['client_id'].'", "'.$row['FullName'].'", "'.$row['Organisation'].'", "'.$row['Email'].'", "'.$row['Tel'].'", "'.$row['Fax'].'", "'.$row['Address'].'", "'.$row['Address2'].'", "'.$row['Address3'].'", "'.$row['Town'].'",
						"'.$row['County'].'", "'.$row['Postcode'].'", "'.$row['Country'].'", "'.$row['ShippingName'].'", "'.$row['ShippingCompany'].'", "'.$row['ShippingAddress'].'", "'.$row['ShippingAddress2'].'", "'.$row['ShippingTown'].'", 
						"'.$row['ShippingCounty'].'", "'.$row['ShippingPostcode'].'", "'.$row['ShippingCountry'].'")';
		$RESULT3 = mysqli_query($mysqli, $SQL3);
		if ($DEBUG) { echo str_replace("\t", "", $SQL3)."\n"; }
	}
}


//ANIMALS
$SQL = "SELECT GROUP_CONCAT(AnimalID) as animal_ids, min(AnimalID) as AnimalID, ClientID, Species, Breed, 
		RegisteredName, Registration, Sex, date_BirthDate, TatooOrChip, Colour, PetName 
		FROM webshop_import WHERE imported=0 GROUP BY RegisteredName, Registration, PetName, date_BirthDate, TatooOrChip";
if ($DEBUG) { echo str_replace("\t", "", $SQL)."\n"; }

$RESULT = mysqli_query($mysqli, $SQL);
while ($row = mysqli_fetch_assoc($RESULT)){
	//echo implode("\t", array($row['RegisteredName'], $row['Registration'], $row['date_BirthDate'], $row['TatooOrChip']))."\n";
	
	$SQL2 = 'SELECT * FROM animal WHERE 
			(RegisteredName = "'.$row['RegisteredName'].'") + 
			(PetName = "'.$row['PetName'].'") + 
			(Registration = "'.$row['Registration'].'") + 
			(BirthDate = "'.$row['date_BirthDate'].'") + 
			(TatooOrChip = "'.$row['TatooOrChip'].'") > 3';
	if ($DEBUG) { echo str_replace("\t", "", $SQL2)."\n"; }
	$RESULT2 = mysqli_query($mysqli, $SQL2);
	if (mysqli_num_rows($RESULT2) > 0){
		echo "Animal already found in the database - record needs updating\n";
	}
	else{
		echo "Animal needs a record creating in the database\n";
		$SQL3 = 'INSERT INTO animal(webshop_animal_ids, AnimalID, ClientID, Species, Breed, RegisteredName, Registration, Sex, TatooOrChip, BirthDate, PetName, Colour)
				VALUES ("'.$row['animal_ids'].'", '.$row['AnimalID'].', '.$row['ClientID'].', "'.$SPECIES[$row['Species']].'", "'.$row['Breed'].'", "'.$row['RegisteredName'].'",
						"'.$row['Registration'].'", "'.$row['Sex'].'",  "'.$row['TatooOrChip'].'", "'.$row['date_BirthDate'].'", "'.$row['PetName'].'", "'.$row['Colour'].'")';
		$RESULT3 = mysqli_query($mysqli, $SQL3);
		if ($DEBUG) { echo str_replace("\t", "", $SQL3)."\n"; }
	}
}

?>
