const CategoriesList = ({ categories, onEdit, onDelete }) => {
    return (
        <div className="">
            <h3 className="text-lg font-semibold mb-4">Your Categories</h3>

            <ul className="space-y-3">
                {categories.map((cat) => (
                    <li
                        key={cat.id}
                        className="
                            flex flex-col sm:flex-row 
                            sm:items-center sm:justify-between
                            border-b pb-3
                            gap-3
                        "
                    >
                        <div className="flex items-center gap-3 flex-wrap">
                            <span
                                className="h-4 w-4 rounded"
                                style={{ backgroundColor: cat.color }}
                            />

                            <span className="font-medium break-words">
                                {cat.name}
                            </span>

                            <span className="text-sm text-gray-500">
                                ({cat.type})
                            </span>
                        </div>

                        <div className="space-y-3 space-x-2">
                            <button
                                onClick={() => onEdit(cat)}
                                className="
                                    text-indigo-600 hover:text-indigo-800 hover:underline 
                                    text-sm font-medium
                                    sm:self-auto self-start
                                "
                            >
                                Edit
                            </button>
                            <button
                                onClick={() => onDelete(cat)}
                                className="
                                    text-red-600 hover:text-red-800 hover:underline 
                                    text-sm font-medium
                                    sm:self-auto self-start
                                "
                            >
                                Delete
                            </button>
                        </div>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default CategoriesList;