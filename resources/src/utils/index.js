export const toggleFullScreen = () => {
    let doc = window.document;
    let docEl = doc.documentElement;

    let requestFullScreen =
        docEl.requestFullscreen ||
        docEl.mozRequestFullScreen ||
        docEl.webkitRequestFullScreen ||
        docEl.msRequestFullscreen;
    let cancelFullScreen =
        doc.exitFullscreen ||
        doc.mozCancelFullScreen ||
        doc.webkitExitFullscreen ||
        doc.msExitFullscreen;

    if (
        !doc.fullscreenElement &&
        !doc.mozFullScreenElement &&
        !doc.webkitFullscreenElement &&
        !doc.msFullscreenElement
    ) {
        requestFullScreen.call(docEl);
    } else {
        cancelFullScreen.call(doc);
    }
};

export const formatNumber = (number, dec = 2) => {
    // Ensure the number is rounded to the specified decimal places
    const roundedNumber = Number(number).toFixed(dec);

    // Convert the rounded number to a string and split it into integer and fractional parts
    const [integerPart, fractionalPart] = roundedNumber.split(".");

    // If decimal places are not required, return the integer part
    if (dec <= 0) return integerPart;

    return `${integerPart}.${fractionalPart}`;
}
//---Validate State Fields
export const getValidationState = ({dirty, validated, valid = null}) => {
    return dirty || validated ? valid : null;
}//------ Toast

export const isNonZeroNumber = (value) => {
    return (value !== undefined &&
        value !== null &&
        value.trim() !== "" &&
        /\d/.test(value) &&
        value !== 0)
}

export const isNotANumber = (value) => {
    // Check if the value is null or undefined
    if (value === null || value === undefined) {
        return true;
    }

    // Convert the value to a number
    const numValue = Number(value);

    // Check if the conversion result is NaN
    if (isNaN(numValue)) {
        return true;
    }

    // Check if the value is an empty string or a whitespace string
    if (typeof value === 'string' && value.trim() === '') {
        return true;
    }

    // Use a stricter regular expression to only allow decimal numbers
    const strictNumberPattern = /^-?\d+(\.\d+)?$/;

    // Convert the value to a string for pattern matching
    const valueAsString = String(value);

    // If the string does not match the strict pattern, it's not a valid number
    return !strictNumberPattern.test(valueAsString);
}

export const randomString = (length) => {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    let result = '';

    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }

    return result;
}

export default {
    toggleFullScreen,
    formatNumber,
    getValidationState,
    isNonZeroNumber,
    isNotANumber,
    randomString
}
