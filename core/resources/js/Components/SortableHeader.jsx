const SortableHeader = ({ label, sortKey, sortBy, sortDir, setSortBy, setSortDir }) => {
    const active = sortBy === sortKey;

    const toggleSort = () => {
        if (!active) {
            setSortBy(sortKey);
            setSortDir("asc");
        } else {
            setSortDir(sortDir === "asc" ? "desc" : "asc");
        }
    };

    return (
        <th
            onClick={toggleSort}
            className="px-4 py-2 text-left text-sm font-medium text-gray-700 cursor-pointer select-none"
        >
            <span className="flex items-center gap-1">
                {label}
                {active && (sortDir === "asc" ? "▲" : "▼")}
            </span>
        </th>
    );
};

export default SortableHeader;