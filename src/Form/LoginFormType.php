<?php
namespace App\Form;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class LoginFormType extends AbstractType
{

    public function __construct(protected RequestStack $requestStack)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class, [
                'csrf_field_name' => '_username',
                'block_name' => '_username',
                'label' => 'Email',
                'constraints' => [
                    new Assert\Email(),
                    new Assert\NotBlank([
                        'message' => 'Enter email'
                    ])
                ]
            ])
            ->add('_password', PasswordType::class, [
                'csrf_field_name' => '_password',
                'block_name' => '_password',
                'label' => 'Password',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter a password',
                    ])
                ]
            ]);

        $request = $this->requestStack->getMainRequest();
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($request) {
            if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
                $error = $request->attributes-get(Security::AUTHENTICATION_ERROR);
            } else {
                $error = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
            }

            if ($error) {
                $event->getForm()->addError(new FormError($error->getMessage()));
            }

            $event->setData(array_replace((array) $event->getData(), [
                '_username' => $request->getSession()->get(Security::LAST_USERNAME)
            ]));
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'authenticate',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}