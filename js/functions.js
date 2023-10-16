function formatDate(dateString) {
    let date = new Date(dateString);
    let day = date.getDate();
    //var month = date.getMonth() + 1;
    let month = date.toLocaleString('en-US', {month: 'long'});
    let year = date.getFullYear();

    // Add leading zeros if needed
    //if (day < 10) day = '0' + day;
    //if (month < 10) month = '0' + month;

    return day + ' ' + month + ', ' + year;
}
