import { useState, useRef, useEffect } from "react";
import InputLabel from "./InputLabel";
const Dropdown = ({
    label,
    value,
    onChange,
    options,
    placeholder = "Select...",
}) => {
    const [open, setOpen] = useState(false);
    const ref = useRef();

    // Close dropdown when clicking outside
    useEffect(() => {
        function handleClick(e) {
            if (ref.current && !ref.current.contains(e.target)) {
                setOpen(false);
            }
        }
        document.addEventListener("mousedown", handleClick);
        return () => document.removeEventListener("mousedown", handleClick);
    }, []);

    return (
        <div className="relative" ref={ref}>
            {label && <InputLabel value={label} />}

            {/* Trigger */}
            <div
                onClick={() => setOpen(!open)}
                className="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm cursor-pointer flex justify-between items-center"
            >
                <span className="text-gray-700">
                    {value ? value.label ?? value : placeholder}
                </span>

                <svg
                    className="h-4 w-4 text-gray-500"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </div>

            {/* Menu */}
            {open && (
                <div className="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg border border-gray-200">
                    {options.map((opt) => (
                        <button
                            key={opt.value}
                            type="button"
                            onClick={() => {
                                onChange(opt.value);
                                setOpen(false);
                            }}
                            className="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                        >
                            {opt.label}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}

export default Dropdown;