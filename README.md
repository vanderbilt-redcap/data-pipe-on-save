# Data Pipe on Save
Piping data from a list of fields to another list of fields. Can save to the same project or another. Can be set up to only pipe on a trigger field and value. Requires specifying the record ID where the data will pipe, using REDCap data piping formatting.

###Explanation of module settings:

**"Destination Projects"** - Repeating group of settings that define the project(s) that data should be piped to when this project is saved.<br>
**"Project in Which to Generate New Record"** - A dropdown list of projects that you have access to in REDCap. If the project does not show up in this dropdown, ensure that you are assigned to that project as a user.<br>
**"Field to Trigger Record Generation (leave blank to pipe on every save)"** - Dropdown list of fields on the REDCap project. This can be left blank if data piping needs to happen every time a record is saved. Otherwise, you need to specify a field that needs to have a value in order for piping to be triggered upon saving a record.<br>
**"Specific value in flagging field to trigger data piping"** - Stored value required in the trigger field defined above to trigger data piping process. Leave this blank if any value should trigger the data piping. NOTE: Use stored values instead of data labels for this (EX: For a Yes/No field, do not use "Yes", use "1").<br>
**"Name for New Record (uses standard REDCap piping)"** - Use this to define the ID that should be set for the record being created in the destination project. This setting is required. Standard REDCap piping syntax can be used to set a record ID based on the data values of fields in the record being saved.<br>
**"Overwrite data in destination project record every time data is saved in this project"** - Choose "Yes" in this setting if the data saved in records in this project should overwrite the data in the record being piped to in the destination project. If "No" is selected here, data piping will only be used to create records in the destination project, and existing records will not have their data overwritten by this process.<br>
**"Data Field Mappings of Source Data to Destination Data. Data will not be piped if the source field is trying to push a value to a destination field that cannot accept it"** - Heading for a repeating group of settings that define the data fields that are being piped into in the destination project. If no data fields are specified here, then the module will match every field with matching name, field type, and validation types, and attempt to pipe the data into the destination projects for all these fields.<br>
**"Source Field"** - Dropdown of fields in this project to choose which one to pull the value from.<br>
**"Destination Field"** - Field name in the destination project to have data piped into. This data piping will not happen if the field type, enumeratted values, and validation settings do not match.<br> 