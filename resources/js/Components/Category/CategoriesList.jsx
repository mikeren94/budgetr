const CategoriesList = ({ categories, onEdit }) => {
    return (
        <div className="bg-white rounded-lg shadow p-4">
            <h3 className="text-lg font-semibold mb-4">Your Categories</h3>

            <ul className="space-y-3">
                {categories.map((cat) => (
                    <li
                        key={cat.id}
                        className="flex items-center justify-between border-b pb-2"
                    >
                        <div className="flex items-center gap-3">
                            <span
                                className="h-4 w-4 rounded"
                                style={{ backgroundColor: cat.color }}
                            />
                            <span className="font-medium">{cat.name}</span>
                            <span className="text-sm text-gray-500">
                                ({cat.type})
                            </span>
                        </div>

                        <button
                            onClick={() => onEdit(cat)}
                            className="text-indigo-600 hover:text-indigo-800 text-sm"
                        >
                            Edit
                        </button>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default CategoriesList;