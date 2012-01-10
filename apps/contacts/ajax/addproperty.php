<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Init owncloud
require_once('../../../lib/base.php');

// Check if we are a user
OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('contacts');

$id = $_POST['id'];
$vcard = OC_Contacts_App::getContactVCard( $id );

$name = $_POST['name'];
$value = $_POST['value'];
$parameters = isset($_POST['parameters'])?$_POST['parameters']:array();

$property = $vcard->addProperty($name, $value); //, $parameters);

$line = count($vcard->children) - 1;

// Apparently Sabre_VObject_Parameter doesn't do well with multiple values or I don't know how to do it. Tanghus.
foreach ($parameters as $key=>$element) {
	if(is_array($element) && strtoupper($key) == 'TYPE') { 
		// FIXME: Maybe this doesn't only apply for TYPE?
		// And it probably shouldn't be done here anyways :-/
		foreach($element as $e){
			if($e != '' && !is_null($e)){
				$vcard->children[$line]->parameters[] = new Sabre_VObject_Parameter($key,$e);
			}
		}
	} else {
			$vcard->children[$line]->parameters[] = new Sabre_VObject_Parameter($key,$element);
	}
}

OC_Contacts_VCard::edit($id,$vcard->serialize());

$adr_types = OC_Contacts_App::getTypesOfProperty('ADR');
$phone_types = OC_Contacts_App::getTypesOfProperty('TEL');

$tmpl = new OC_Template('contacts','part.property');
$tmpl->assign('adr_types',$adr_types);
$tmpl->assign('phone_types',$phone_types);
$tmpl->assign('property',OC_Contacts_VCard::structureProperty($property,$line));
$page = $tmpl->fetchPage();

OC_JSON::success(array('data' => array( 'page' => $page )));
