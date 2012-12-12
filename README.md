# DISPEDIA - An OntoWiki extension

## Way 1 - Predicate / NestedConfig

### Overview

Way 1 contains two elements for configuring your formula. The predicate one is a simple input field. It can be configured by a type definition and by the ontology. You can use it for simple text, birth date or anything else.
The nestedconfig one will be used to include another XML config file. This file can contain predicate and nestedconfig tags too.

Every XML config file is related to one class. All predicate fields will be interpreted as they are belonged to that class!

### Example for "predicate"

    <item>
        <predicate> 
            <mandatory>0</mandatory>
            <predicateuri>architecture:birthdate</predicateuri>
            <type>birthdate</type>
        </predicate>
        <predicate>
            ... 

Every text field will be described by a *predicate* container.

*Mandatory* defines whether a field is required or not. A form with unfilled required fields cant be posted.

*Predicateuri* tag is an URI to predicate definition in the ontology. The interpreter need this definition.

*Type* says the interpreter how to display this field. E.g. "gender" stands for a 2-element list which contains only "female" and "male". For example: You have a date which represents the birth date of a person. In one hand you can use a simple text field, in the other one you can use the jQuery component date.

### Example for "nestedconfig"

In this section you are able to include a XML config file. This file can contain predicates and nestedconfigs too.

    <item>	
        <nestedconfig>
            <target>doctor.xml</target>
            <relations>
                <item>architecture:isSupervisedBy</item>
                <item>architecture:isDoctorOf</item>
            </relations>
        </nestedconfig>
    </item> 

For include a XML config use a nestedconfig.

The *target* is the filename to the XML config file. All config files are stored in folder _formconfigs_.

*Relations* specify relations that will be created between different resources. You have the XML config which includes another one. The parent XML config generates one resource and the children config too. All relations which are defined will be created between the resource of parent XML config and all included children XML configs.

### Complete example form

    <!-- Create a new patient. With own changes ... -->
    <form> 

            <!-- Related class to which all predicates are referenced -->
            <targetclass>architecture:Patient</targetclass>

            <!-- Needed by the URI generation function. -->
            <labelparts>
                    <item>schema:givenName</item>
                    <item>schema:familyName</item>
            </labelparts>
        
            <!-- Headline of this formula -->
            <headline>Patient</headline>
        
            <!-- Introducing text to inform the user about the formula -->
        <introduceText>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</introduceText>
        
        <sections>
            
                <!-- A new section -->
            <item>
                
                    <!-- Caption of this section part -->
                <caption>Patient information</caption> 	
                            
                        <!-- Textfield -->
                <predicate> 
                    <mandatory>1</mandatory>
                    <predicateuri>schema:givenName</predicateuri>
                </predicate>
                
                        <!-- Textfield -->
                <predicate> 
                    <mandatory>1</mandatory>
                    <predicateuri>schema:familyName</predicateuri>
                </predicate>
                
                        <!-- Textfield -->
                <predicate> 
                    <mandatory>0</mandatory>
                    <predicateuri>architecture:birthdate</predicateuri>
                    <type>birthdate</type>
                </predicate>
                    
                        <!-- Textfield -->		
                <predicate> 
                    <mandatory>0</mandatory>
                    <predicateuri>architecture:gender</predicateuri>
                    <type>list</type>
                            <typeparameter>
                                    <item>- please select -</item>
                                    <item>female</item>
                                    <item>male</item>
                            </typeparameter>
                </predicate>
            
            </item>
            
                <!-- Another section ... -->
            <item>	
            
                    <caption>Treating physician</caption>
            
                        <!-- Include a XML file -->
                <nestedconfig>
                    <target>doctor.xml</target>
                    <relations>
                        <item>architecture:isSupervisedBy</item>
                        <item>architecture:isDoctorOf</item>
                    </relations>
                </nestedconfig>
                
            </item> 
        </sections>
    </form>

This XML config will create a formula with four fields, labeled as first- and last name, birthdate and gender. All predicate tags are belonged to _architecture:Person_.

## Field types

### Simple text field

If nothing was defined or no definition was found the interpreter will display a simple text field.

### List

A list is a simple collection. With *typeparameter* tag you are able to set the elements. Here is a short example which will generate a list of 3 elements: _- please select -, female and male_.

	<!-- Textfield -->		
	<predicate> 
		<mandatory>0</mandatory>
		<predicateuri>architecture:gender</predicateuri>
		<type>list</type>
        	<typeparameter>
                	<item>- please select -</item>
                    	<item>female</item>
                    	<item>male</item>
                </typeparameter>
	</predicate>

### Birthdate

Its a special kind of a date. There are three select boxes where you must choose day, month and year of the birthdate.

