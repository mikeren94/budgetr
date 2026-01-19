import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link } from '@inertiajs/react';
import copy from '../../data/default_copy.json';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="Welcome" />

            <div className="min-h-screen bg-gray-50 dark:bg-black flex flex-col justify-center items-center px-6 py-10">

                {/* Logo */}
                <ApplicationLogo className="h-14 w-14 text-white mb-6" />

                {/* Headline */}
                <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white text-center leading-tight max-w-xs sm:max-w-md">
                    {copy.slogan}
                </h1>

                {/* Subheading */}
                <p className="mt-4 text-base sm:text-lg text-gray-600 dark:text-gray-300 max-w-sm sm:max-w-md text-center leading-relaxed">
                    {copy.welcome_message}
                </p>

                <div className="
    mt-10 
    w-full max-w-xs 
    sm:max-w-md 
    flex flex-col sm:flex-row 
    gap-4 sm:gap-6 
    justify-center 
    items-center
">
                    {auth.user ? (
                        <Link
                            href={route('dashboard')}
                            className="w-full sm:w-auto text-center px-6 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition"
                        >
                            Go to Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={route('login')}
                                className="w-full sm:w-auto text-center px-6 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition"
                            >
                                Log In
                            </Link>

                            <Link
                                href={route('register')}
                                className="w-full sm:w-auto text-center px-6 py-3 rounded-lg border border-indigo-600 text-indigo-600 font-semibold hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition"
                            >
                                Create Account
                            </Link>
                        </>
                    )}
                </div>

                {/* Footer */}
                <footer className="mt-16 text-sm text-gray-500 dark:text-gray-400 text-center">
                    Built with Laravel & Inertia
                </footer>
            </div>
        </>
    );
}