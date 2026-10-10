import { Head, Link, useForm } from '@inertiajs/react';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import FormActions from '@/components/ui/form-actions';
import FormField from '@/components/ui/form-field';
import ImageUpload from '@/components/ui/image-upload';
import Input from '@/components/ui/input';
import PageHeader from '@/components/ui/page-header';
import Select from '@/components/ui/select';
import Textarea from '@/components/ui/textarea';

export default function EditVendorProfile({ profile, businessTypes, statuses, logoRules }) {
    const { data, setData, post, processing, errors, progress, isDirty } = useForm({
        _method: 'put',
        business_name: profile?.business_name ?? '',
        organization_name: profile?.organization_name ?? '',
        owner_name: profile?.owner_name ?? '',
        phone: profile?.phone ?? '',
        email: profile?.email ?? '',
        address: profile?.address ?? '',
        city: profile?.city ?? '',
        business_type: profile?.business_type ?? '',
        vat_tin: profile?.vat_tin ?? '',
        status: profile?.status ?? 'active',
        logo: null,
        remove_logo: false,
    });

    const cancelUrl = route('vendor-profile.show');
    const logoTypes = logoRules.mimes.map((type) => type.toUpperCase()).join(', ');
    const logoMaxMb = Math.round((logoRules.max_kb / 1024) * 10) / 10;

    function submit(event) {
        event.preventDefault();
        // Files need multipart form data, which only POST supports; Laravel
        // reads the _method field and routes this as PUT.
        post(route('vendor-profile.update'), { forceFormData: true, preserveScroll: true });
    }

    function field(name) {
        return {
            value: data[name],
            onChange: (event) => setData(name, event.target.value),
        };
    }

    return (
        <>
            <Head title="Edit vendor profile" />

            <PageHeader
                title={profile ? 'Edit vendor profile' : 'Set up vendor profile'}
                description="These details appear in the app header, statements and reports."
                back={{ href: cancelUrl, label: 'Vendor profile' }}
            />

            <form onSubmit={submit} noValidate className="space-y-6">
                <Card>
                    <CardHeader title="Business information" />
                    <CardBody className="grid gap-5 sm:grid-cols-2">
                        <FormField name="business_name" label="Business name" error={errors.business_name} required>
                            <Input autoComplete="organization" {...field('business_name')} />
                        </FormField>
                        <FormField
                            name="organization_name"
                            label="Organization name"
                            hint="Registered or legal name, if different."
                            error={errors.organization_name}
                        >
                            <Input {...field('organization_name')} />
                        </FormField>
                        <FormField
                            name="business_type"
                            label="Business type"
                            hint="Pick a suggestion or type your own."
                            error={errors.business_type}
                        >
                            <Input list="business-type-options" autoComplete="off" {...field('business_type')} />
                        </FormField>
                        <datalist id="business-type-options">
                            {businessTypes.map((type) => (
                                <option key={type} value={type} />
                            ))}
                        </datalist>
                        <FormField name="vat_tin" label="VAT / TIN" hint="Optional." error={errors.vat_tin}>
                            <Input autoComplete="off" {...field('vat_tin')} />
                        </FormField>
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Owner & contact" />
                    <CardBody className="grid gap-5 sm:grid-cols-2">
                        <FormField name="owner_name" label="Owner name" error={errors.owner_name} required className="sm:col-span-2">
                            <Input autoComplete="name" {...field('owner_name')} />
                        </FormField>
                        <FormField name="phone" label="Phone" error={errors.phone} required>
                            <Input type="tel" inputMode="tel" autoComplete="tel" {...field('phone')} />
                        </FormField>
                        <FormField name="email" label="Email" error={errors.email} required>
                            <Input type="email" autoComplete="email" {...field('email')} />
                        </FormField>
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Address" />
                    <CardBody className="grid gap-5 sm:grid-cols-2">
                        <FormField name="address" label="Address" error={errors.address} required className="sm:col-span-2">
                            <Textarea rows={3} autoComplete="street-address" {...field('address')} />
                        </FormField>
                        <FormField name="city" label="City" error={errors.city} required>
                            <Input autoComplete="address-level2" {...field('city')} />
                        </FormField>
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Branding & status" />
                    <CardBody className="grid gap-6 sm:grid-cols-2">
                        <ImageUpload
                            label="Logo"
                            name={data.business_name}
                            file={data.logo}
                            currentUrl={profile?.logo_url}
                            removed={data.remove_logo}
                            accept={logoRules.mimes.map((type) => `.${type}`).join(',')}
                            hint={`${logoTypes}, up to ${logoMaxMb} MB. A square image looks best.`}
                            error={errors.logo}
                            onSelect={(file) => setData((current) => ({ ...current, logo: file, remove_logo: false }))}
                            onRemove={() => setData((current) => ({ ...current, logo: null, remove_logo: true }))}
                        />
                        <FormField name="status" label="Status" error={errors.status} required>
                            <Select options={statuses} {...field('status')} />
                        </FormField>
                    </CardBody>
                    {progress && (
                        <div className="px-4 pb-5 sm:px-6">
                            <progress value={progress.percentage} max="100" className="h-1.5 w-full accent-primary-600">
                                {progress.percentage}%
                            </progress>
                        </div>
                    )}
                </Card>

                <FormActions>
                    <Button as={Link} href={cancelUrl} variant="secondary">
                        Cancel
                    </Button>
                    <Button type="submit" loading={processing} disabled={!isDirty}>
                        Save profile
                    </Button>
                </FormActions>
            </form>
        </>
    );
}
