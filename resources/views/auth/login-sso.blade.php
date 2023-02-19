<x-guest-layout>

    <div class="flex flex-row justify-center min-h-[calc(100vh-8rem)] mt-5">
        <div class="h-96 w-2/3 my-2 mx-4 rounded-box bg-base-200 ">
            <div class="hero">
                <div class="hero-content flex-col lg:flex-row">
                    <x-application-logo width="200pt" height="200pt" class="fill-current text-gray-500"/>
                    <div>
                        <h1 class="text-5xl font-bold">{{__('PM2ETML LOGIN')}}</h1>
                        <div class="flex flex-col items-center mb-1.5">
                            <img src="img/unlock.gif" width="125px" />
                        </div>
                        <div class="text-center">
                            <a class="btn btn-primary {{$errors->any()?'btn-disabled':''}}" href="{{route('sso-login-redirect')}}">
                                <img src="https://www.microsoft.com/favicon.ico" alt="" width="24" height="24"
                                     class="mr-1.5">
                                {{__('Authenticate through Eduvaud')}}
                            </a>
                            <x-auth-validation-errors class="mb-4" :errors="$errors" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-guest-layout>
