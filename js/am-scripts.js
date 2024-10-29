function ClipBoard() {
    acronym__export.innerText = copytext.innerText;
    Copied = acronym__export.createTextRange();
    Copied.execCommand("Copy");
}

function check_file(){
    str=document.getElementById('acronym_file_name').value.toUpperCase();
    suffix=".AMF";
    suffix2=".TXT";

    // for i18n $('#file-type-error').val(objectL10n.file-type-error)
    if(!(str.indexOf(suffix, str.length - suffix.length) !== -1 || str.indexOf(suffix2, str.length - suffix2.length) !== -1)){
        alert( "File type not allowed,\nAllowed file types: *.amf,*.txt" );
        document.getElementById('acronym_file_name').value='';
    }
}
