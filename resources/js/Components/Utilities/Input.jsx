const Input = ({ className = "", ...props }) => {
    return (
        <input
            {...props}
            className={
                "w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 " +
                className
            }
        />
    );
}

export default Input;