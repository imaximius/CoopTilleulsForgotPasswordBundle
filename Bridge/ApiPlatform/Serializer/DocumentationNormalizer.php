<?php

/*
 * This file is part of the CoopTilleulsForgotPasswordBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\ForgotPasswordBundle\Bridge\ApiPlatform\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class DocumentationNormalizer implements NormalizerInterface
{
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        // Add POST /forgot-password/ path
        $docs['tags'][] = ['name' => 'Forgot password'];
        $docs['paths']['/api/forgot-password/']['post'] = [
            'tags' => ['Forgot password'],
            'operationId' => 'postForgotPassword',
            'summary' => 'Generates a token and send email',
            'responses' => [
                201 => [
                    'description' => 'Valid email address, token generated and sent',
                ],
                404 => [
                    'description' => 'Valid email address, but user is not found',
                ],
                400 => [
                    'description' => 'Missing email parameter or invalid format',
                ],
            ],
            'requestBody' => [
                'description' => 'Request a new password',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ForgotPassword:request',
                        ],
                    ],
                ],
            ],
        ];
        $docs['components']['schemas']['ForgotPassword:request'] = [
            'type' => 'object',
            'description' => '',
            'required' => ['email'],
            'properties' => [
                'email' => [
                    'type' => 'string',
                ],
            ],
        ];

        // Add GET /forgot-password/{token} path
        $docs['paths']['/api/forgot-password/{token}']['get'] = [
            'tags' => ['Forgot password'],
            'operationId' => 'getForgotPassword',
            'summary' => 'Validates token',
            'responses' => [
                200 => [
                    'description' => 'Authenticated user',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/ForgotPassword:validate',
                            ],
                        ],
                    ],
                ],
                404 => [
                    'description' => 'Token not found or expired',
                ],
            ],
            'parameters' => [
                [
                    'name' => 'token',
                    'in' => 'path',
                    'required' => true,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ];
        $docs['components']['schemas']['ForgotPassword:validate'] = [
            'type' => 'object',
            'description' => '',
        ];

        // Add POST /forgot-password/{token} path
        $docs['paths']['/api/forgot-password/{token}']['post'] = [
            'tags' => ['Forgot password'],
            'operationId' => 'postForgotPasswordToken',
            'summary' => 'Resets user password from token',
            'responses' => [
                202 => [
                    'description' => 'Password updated',
                ],
                400 => [
                    'description' => 'Missing password parameter',
                ],
                404 => [
                    'description' => 'Token not found',
                ],
            ],
            'parameters' => [
                [
                    'name' => 'token',
                    'in' => 'path',
                    'required' => true,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'requestBody' => [
                'description' => 'Reset password',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ForgotPassword:reset',
                        ],
                    ],
                ],
            ],
        ];
        $docs['components']['schemas']['ForgotPassword:reset'] = [
            'type' => 'object',
            'description' => '',
            'required' => ['password'],
            'properties' => [
                'password' => [
                    'type' => 'string',
                ],
            ],
        ];

        return $docs;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
