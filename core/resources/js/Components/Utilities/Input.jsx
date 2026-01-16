const Input = ({ type = "text", className = "", ...props }) => {
    let baseClasses = "";

    if (type === "checkbox") {
        baseClasses = "h-4 w-4";
    } else if (type === "color") {
        baseClasses = "h-8 w-12 p-0 border-gray-400 rounded cursor-pointer";
    } else {
        baseClasses = "w-full border-gray-400 rounded px-3 py-2";
    }

    return <input type={type} className={`${baseClasses} ${className}`} {...props} />;
};

export default Input;