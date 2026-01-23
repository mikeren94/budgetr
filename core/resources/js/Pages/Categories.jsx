import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import axios from 'axios';

import SubmitCategory from '@/Components/Category/SubmitCategory';
import CategoriesList from '@/Components/Category/CategoriesList';
import { useCategories } from '@/hooks/useCategories';

const Categories = () => {
    const [categoryToEdit, setCategoryToEdit] = useState(null);

    const {
        categories,
        loading,
        refreshCategories
    } = useCategories();

    const onDeleteCategory = async (category) => {
        const confirmed = window.confirm('Are you sure you want to delete this category?');
        if (!confirmed) return;

        try {
            await axios.delete(`/api/categories/${category.id}`, { withCredentials: true });
            refreshCategories();
        } catch (error) {
            console.error('Failed to delete category', error);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Categories
                </h2>
            }
        >
            <Head title="Categories" />

            <div className="pt-4">
                <div className="grid grid-cols-1 lg:grid-cols-6 gap-6">

                    <div className="col-span-1 lg:col-span-2 bg-white rounded-lg shadow p-4">
                        <SubmitCategory
                            category={categoryToEdit}
                            onSuccess={() => {
                                refreshCategories();
                                setCategoryToEdit(null);
                            }}
                        />
                    </div>

                    <div className="col-span-1 lg:col-span-4 bg-white rounded-lg shadow p-4">
                        {loading ? (
                            <p>Loading categories…</p>
                        ) : (
                            <CategoriesList
                                categories={categories}
                                onEdit={setCategoryToEdit}
                                onDelete={onDeleteCategory}
                            />
                        )}
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Categories;