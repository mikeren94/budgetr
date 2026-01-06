const MonthSelector = ({ selected = [], onChange }) => {
    const months = [
        { num: 1, label: "Jan" },
        { num: 2, label: "Feb" },
        { num: 3, label: "Mar" },
        { num: 4, label: "Apr" },
        { num: 5, label: "May" },
        { num: 6, label: "Jun" },
        { num: 7, label: "Jul" },
        { num: 8, label: "Aug" },
        { num: 9, label: "Sep" },
        { num: 10, label: "Oct" },
        { num: 11, label: "Nov" },
        { num: 12, label: "Dec" },
    ];

    const toggleMonth = (num) => {
        if (selected.includes(num)) {
            onChange(selected.filter((m) => m !== num));
        } else {
            onChange([...selected, num]);
        }
    };

    return (
        <div className="grid grid-cols-3 gap-2">
            {months.map(({ num, label }) => {
                const isActive = selected.includes(num);

                return (
                    <button
                        key={num}
                        type="button"
                        onClick={() => toggleMonth(num)}
                        className={`px-3 py-2 rounded border text-sm transition
                            ${isActive 
                                ? "bg-blue-600 text-white border-blue-600" 
                                : "bg-white text-gray-700 border-gray-300 hover:bg-gray-100"
                            }
                        `}
                    >
                        {label}
                    </button>
                );
            })}
        </div>
    );
};

export default MonthSelector;