<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->label(__('Názov'))
                ->maxLength(50)
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    $set('slug', Str::slug($state));
                })
                ->unique(Category::class, 'name', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                    return $rule->where('deleted_at', null);
                })
                ->required(),

                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->disabled()
                    ->unique(Category::class, 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('deleted_at', null);
                    }),

                Toggle::make('active')
                    ->label(__('Aktívny'))
                    ->required()
                    ->rules([
                        'boolean'
                    ]),

                RichEditor::make('description')
                    ->label(__('Popis'))
                    ->columnSpan('full'),

                SpatieMediaLibraryFileUpload::make('image')
                    ->label(__('Nahrať obrázok'))
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('Obrázok')),

                TextColumn::make('name')
                    ->label(__('Názov'))
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label(__('Aktívny'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Category $record) {
                        $record->products()->detach();
                        $record->delete();
                    }),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make()
                    ->before(function (Category $record, $action) {
                        $exists = Category::query()
                            ->where('name', $record->name)
                            ->where('slug', $record->slug)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title(__('Obnova zlyhala'))
                                ->body(__('Kategória s rovnakým názvom alebo slug-om už existuje.'))
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->recordUrl(function (Category $record): string {
                return self::getUrl('view', ['record' => $record]);
            });
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
