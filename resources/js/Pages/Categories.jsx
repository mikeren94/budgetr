import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import SubmitCategory from '@/Components/Category/SubmitCategory';
import CategoriesList from '@/Components/Category/CategoriesList';
import { useCategories } from '@/Hooks/useCategories';

const Categories = () => {
    const [categoryToEdit, setCategoryToEdit] = useState(null);

    const {
        categories,
        loading,
        refreshCategories
    } = useCategories();

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Categories
                </h2>
            }
        >
            <Head title="Categories" />

            <div className="py-12">
                <div className="grid grid-cols-1 lg:grid-cols-6 gap-6 px-6">

                    {/* Left column: form */}
                    <div className="col-span-1 lg:col-span-2 bg-white rounded-lg shadow p-4">
                        <SubmitCategory
                            category={categoryToEdit}
                            onSuccess={() => {
                                refreshCategories();
                                setCategoryToEdit(null);
                            }}
                        />
                    </div>

                    {/* Right column: list */}
                    <div className="col-span-1 lg:col-span-4 bg-white rounded-lg shadow p-4">
                        {loading ? (
                            <p>Loading categories…</p>
                        ) : (
                            <CategoriesList
                                categories={categories}
                                onEdit={setCategoryToEdit}
                            />
                        )}
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Categories;