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

function getScreenBreakpoint() {
    var width = $(window).width();

    if (width >= 1200) {
        return 'lg'; // Large screens
    } else if (width >= 992) {
        return 'md'; // Medium screens
    } else if (width >= 768) {
        return 'sm'; // Small screens
    } else {
        return 'xs'; // Extra small screens
    }
}

// used by the stock slider
function daysSincePreviousMonday() {
    const today = new Date();
    const dayOfWeek = today.getDay(); // 0 is Sunday, 1 is Monday, and so on

    // Calculate the number of days since the previous Monday
    const daysSinceMonday = (dayOfWeek + 6) % 7;

    return daysSinceMonday + 7;
}
